<?php

namespace App\Http\Controllers\portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductModel;
use App\Models\Categories;
use App\Models\VendorModel;
use App\Models\Stores;
use App\CustomRequestModel;
use App\RequestModel;
use App\Models\ProductDocsModel;
use App\Models\Brands;
use Validator;
use DB;
use App\Models\ProductAttribute;
use App\Models\Common;
use App\Traits\StoreImageTrait;
use ZipArchive;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Imports\ProductImport;
use App\Exports\ExportReports;
use Maatwebsite\Excel\Facades\Excel;
use Auth;

class ProductImportExport extends Controller
{
    //
    public function import_export(){
      $page_heading= "Product Import Export";
      $companies = [];
      $category_ids = [];
      $stores = [];
      $store_id = '';
      $_current_user = [];
      $id = 0;
      

      $parent_categories_list = $parent_categories = Categories::where(['deleted'=>0,'active'=>1,'parent_id'=>0])->get()->toArray();
        $parent_categories_list = Categories::where(['deleted'=>0,'active'=>1])->where('parent_id','!=',0)->get()->toArray();

        $parent_categories = array_column($parent_categories, 'name', 'id');
        asort($parent_categories);
        $category_list = $parent_categories;

        $sub_categories = [];
        foreach ($parent_categories_list as $row) {
            $sub_categories[$row['parent_id']][$row['id']] = $row['name'];
        }
        $sub_category_list = $sub_categories;
      return view('portal.product.product_import',compact('companies','stores','id','store_id','_current_user','category_list','sub_category_list','category_ids','page_heading'));
    }
    public function upload_file(REQUEST $request)
    {
      $path1 = $request->file('file')->store('temp');
      $path=storage_path('app').'/'.$path1;
      $request->session()->put(['xlsx_full_path'=>$path,'xlsx_pending'=>1]);
      //$_SESSION['xlsx_full_path'] = $path;
      //$_SESSION['xlsx_pending']   = 1;
      echo $path;
    }
    public function filterCell( $val, $default='' )
    {
        if (($val == "#N/A") || (substr($val, 0, 3) == "Err") || ($val == "#VALUE!") || ($val == "#REF!") ) {
            return $default;
        }

        return $val;
    }
    public function send_message($id, $message, $progress, $total=0, $success=0, $failed=0)
    {
        $d = array('message' => $message , 'progress' => $progress, 'total' => $total, 'success' => $success, 'failed' => $failed);

        echo "id: $id" . PHP_EOL;
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;

        ob_flush();
        flush();
    }

    public function import_process_progress()
    {
        $this->output->set_header('Cache-Control: post-check=0, pre-check=0');
        $this->output->set_header('Content-Type:text/event-stream');
        $this->output->set_content_type('text/event-stream', 'UTF-8');
        header('Content-Type: text/event-stream');
        // recommended to prevent caching of event data.
        header('Cache-Control: no-cache');

        $success = 0;
        $failed  = 0;
        for($i=0; $i<50; $i++) {
            if ( $i%2 == 0 ) {
                $success++;
            } else {
                $failed++;
            }
            $this->send_message($i, 'on iteration ' . $i . ' of 50' , $i, 50, $success, $failed);
            usleep(10000 * 10);
        }

        $this->send_message('CLOSE', 'Process complete',"50", 50, $success, $failed);
    }
    public function start_import(REQUEST $request){
      $success = 0;
      $failed = 0;
      header('Content-Type: text/event-stream');
        // recommended to prevent caching of event data.
        header('Cache-Control: no-cache');

        $file_path = session('xlsx_full_path') ?? '';
        $file_pending = session('xlsx_pending') ?? 0;
        if ( ($file_pending > 0) && (file_exists($file_path) !== FALSE) ) {
          $data = Excel::toArray(new ProductImport, $file_path);
          $temp_product_id='';
          $success   = 0;
          $failed    = 0;
          $totalRows = count($data);
          if(isset($data[0][1]))
          {


                 $attribute = ProductModel:: getProductAttributes();
             $attribute_values = [];
              foreach ($attribute as $attr_val) {
                  $attribute_values[$attr_val->attribute_id][$attr_val->attribute_values_id] = $attr_val->attribute_values;
              }

              foreach ($attribute_values as $attribute_id => $attr_val) {
                  ksort($attr_val);
                  $attribute_values[$attribute_id] = $attr_val;
              }

               $attributes=[];
                $attributes = array_column($attribute->toArray(), 'attribute_id', 'attribute_name');
                asort($attributes);


                 $attribute_cols = [];

                $attr_col_start = 20;
                 $attr_col = 0;
                 $status_col_label = '';

           foreach($data[0] as $key => $row)
           {
            $flag=0;

             if ($key == 0) {
                         foreach ($row as $col_name => $attr_name) {
                             $t_name = explode(' ', $attr_name);
                             if ($t_name[0] == 'Attribute') {
                                 $p_name = trim(str_replace($t_name[0], '', $attr_name));
                                 $attribute_cols[$col_name] = $p_name;
                                 $attr_col++;
                             }
                         }
                         $extra_col_num = $attr_col_start+$attr_col;
                         $status_col_label = Coordinate::stringFromColumnIndex($extra_col_num);
                         continue;
             }



             if($row[0]!='' && $row[1]!='' && $row[6]!='' && $row[7]!='' && $row[8]!=''){
                $temp_product_id='';
                $flag=0;
            }

            // INSERT MULTIPLE CATEGORY
            if($row[0]=='' && $row[1]!='' && $temp_product_id!=''){
                 $category_id=explode('#',$row[1]);
                 $category_data = array('product_id'=>$temp_product_id,'category_id'=>$category_id[0]);
                 DB::table('product_category')->insert($category_data);
                 $flag=1;
            }

             if ($row[0]=='' && $row[6]!='' && $row[7]!='' && $row[8]!='') {
                       $product_id=$temp_product_id;
                         $row_variants = [];
                         foreach ($attribute_cols as $t_col_name => $t_attr_name) {
                             $t_attr_val = $this->filterCell($row[$t_col_name]);

                             if (! empty($t_attr_val) ) {
                                 $a=explode('#',$t_attr_val);
                                 $t_attr_val=$a[1];
                                 $t_attribute_id = $attributes[$t_attr_name];
                                 $t_attribute_val_id =$a[0];

                                     $row_variants[] = [
                                         'attribute_id' => $t_attribute_id,
                                         'attribute_values_id' => (int)$t_attribute_val_id,
                                         'product_id'=>$product_id
                                     ];


                             }
                         }

                         if ( !empty($row_variants)) {


                               $product_multiple_variant = [
                                   'regular_price' => $row[6],
                                   'sale_price' =>$row[7],
                                   'product_id' =>$product_id,
                                   'stock_quantity' =>$row[8],
                                   'taxable'=>($row[9]=="YES")?1:0,
                                   'product_full_descr' =>$row[12],
                                   'barcode'  =>$row[10],
                                   'pr_code'  =>$row[11],

                             ];
                          $pAttr = ProductAttribute::create($product_multiple_variant);
                         $product_attribute_id = $pAttr->product_attribute_id;
                                 $product_variations=[];
                                foreach ($row_variants as $key=> $variant) {
                                    $product_variations[] = [
                                         'attribute_id' => $variant['attribute_id'],
                                         'attribute_values_id' => $variant['attribute_values_id'],
                                         'product_id'=>$variant['product_id'],
                                          'product_attribute_id' => $product_attribute_id,
                                     ];
                              }
                              if(!empty($product_variations)){
                               DB::table('product_variations')-> insert($product_variations);
                               DB::table('product_selected_attributes')-> insert($row_variants);
                              }
                         }
                          if($row[19]){
                         $img=[];
                         $image=explode(',',$row[19]);
                          for($i=0;$i<count($image);$i++){
                                    $img[$i]['image']=$image[$i];
                                    $img[$i]['product_id']=$product_id;
                                    $img[$i]['product_attribute_id']=$product_attribute_id;
                              }
                         DB::table('product_temp_image')->insert($img);
                     }
                         $flag=1;
                     }


          if($flag){
              continue;
          }
            $a=explode('#',$row[2]);
            $default_category_id=isset($a[0])?$a[0]:0;

            $a=explode('#',$row[4]);
            $product_vender_id=isset($a[0])?$a[0]:0;

            $insert_data =  [
                    'product_type' => ($row[0]=="Variable")?2:1,
                    'product_name' => $row[3],
                    'product_name_arabic' =>  $row[3],
                    'product_desc_full' => $row[12],
                    'product_desc_full_arabic' =>  $row[12],
                    'product_desc_short' => '',
                    'product_desc_short_arabic' =>  '',
                    'product_unique_iden'=>$row[5],
                    'product_created_by'=>(int)$product_vender_id,
                    'created_at'=> date('Y-m-d H:i:s'),
                    'product_vender_id' =>(int) $product_vender_id,
                    'meta_title' => $row[14],
                    'meta_keyword' => $row[15],
                    'meta_description' => $row[16],
                    'default_category_id' => (int)$default_category_id,
                    'product_status' => 1,
                    'product_vendor_status' => 0,
                    'product_deleted' => 0,
                    'product_variation_type' => 1,
                    'product_taxable' => 1,

            ];

              if(!empty($insert_data))
              {
                $status = $temp_product_id=$product_id= ProductModel::insertGetId($insert_data);
                $spec_title=explode('#',$row[17]);
                $spec_description=explode('#',$row[18]);

                if(!empty($spec_title)){
                 for($i=0;$i<count($spec_title);$i++) {

                        DB::table('product_specifications')->insert(['spec_title'=>$spec_title[$i],'spec_descp'=>isset($spec_description[$i])?$spec_description[$i]:'','product_id'=>$product_id]);
                 }

                 }


               $category_id=explode('#',$row[1]);
                 $category_data = array('product_id'=>$product_id,'category_id'=>(int)$category_id[0]);
                 DB::table('product_category')->insert($category_data);


     // image



             // varients
        if ($row[0]=='Variable' && $row[6]!='' && $row[7]!='' && $row[8]!='') {
                       $product_id=$temp_product_id;
                         $row_variants = [];
                         foreach ($attribute_cols as $t_col_name => $t_attr_name) {
                             $t_attr_val = $this->filterCell($row[$t_col_name]);

                             if (! empty($t_attr_val) ) {
                                 $a=explode('#',$t_attr_val);
                                 $t_attr_val=$a[1];
                                 $t_attribute_id = $attributes[$t_attr_name];
                                 $t_attribute_val_id =$a[0];

                                     $row_variants[] = [
                                         'attribute_id' => $t_attribute_id,
                                         'attribute_values_id' => (int)$t_attribute_val_id,
                                         'product_id'=>$product_id
                                     ];


                             }
                         }

                         if ( !empty($row_variants)) {

                               $product_multiple_variant = [
                                   'regular_price' => $row[6],
                                   'sale_price' =>$row[7],
                                   'product_id' =>$product_id,
                                   'stock_quantity' =>$row[8],
                                   'taxable'=>($row[9]=="YES")?1:0,
                                   'product_full_descr' =>$row[12],
                                   'barcode'  =>$row[10],
                                   'pr_code'  =>$row[11],
                             ];
                         $pAttr = ProductAttribute::create($product_multiple_variant);
                         $product_attribute_id = $pAttr->product_attribute_id;
                                 $product_variations=[];
                                foreach ($row_variants as $key=> $variant) {
                                    $product_variations[] = [
                                         'attribute_id' => $variant['attribute_id'],
                                         'attribute_values_id' => $variant['attribute_values_id'],
                                         'product_id'=>$variant['product_id'],
                                          'product_attribute_id' => $product_attribute_id,
                                     ];
                              }
                              if(!empty($product_variations)){
                               DB::table('product_variations')-> insert($product_variations);
                               DB::table('product_selected_attributes')-> insert($row_variants);
                              }
                         }

                     }
                  else {
                         $product_simple_variant = [
                         'regular_price' => $row[6],
                         'sale_price' =>$row[7],
                         'product_id' =>$product_id,
                         'stock_quantity' =>$row[8],
                         'taxable'=>($row[9]=="YES")?1:0,
                         'product_full_descr' =>$row[12],
                         'barcode'  =>$row[10],
                         'pr_code'  =>$row[11],
                        // 'image' => implode(",",$imagesList)12
                     ];
                                 $pAttr = ProductAttribute::create($product_simple_variant);
                                 $product_attribute_id = $pAttr->product_attribute_id;
                                 ProductModel::updateDefaultAttribute($product_id,$product_attribute_id);
                     }

                      if($row[19]){
                         $img=[];
                         $image=explode(',',$row[19]);
                          for($i=0;$i<count($image);$i++){
                                    $img[$i]['image']=$image[$i];
                                    $img[$i]['product_id']=$product_id;
                                    $img[$i]['product_attribute_id']=$product_attribute_id;
                              }
                         DB::table('product_temp_image')->insert($img);
                     }

                     if ( $status ) {
                         $success++;
                     } else {
                         $failed++;
                     }

                     $this->send_message($i, 'on iteration ' . $i . ' of '.$totalRows, $i, $totalRows, $success, $failed);
                     $i++;
              }

                  //end
           }
           $_SESSION['xlsx_pending'] = 0;
            @unlink($file_path);
            $this->send_message('CLOSE', 'Process complete', $totalRows, $totalRows, $success, $failed);

            //return response()->json(['success' => 1, 'message' => 'Excel Data Imported successfully.']);
           }
        }
    }
    public function upload_zip_file(REQUEST $request){
      $file_name = $_FILES['file']['name'];

      $path =  'products_zip';
      $new_path =  config("global.upload_path").'products/';
      $path1 = $request->file('file')->store($path);
      $path=storage_path('app').'/'.$path1;

      $request->session()->put(['zip_full_path'=>$path,'zip_pending'=>1]);
      echo $path;
    }
    public function startUnzipImage(REQUEST $request){
      $status  = '0';
       $message = '';
       $path =  storage_path('app').'/products_zip/';
       $new_path =  './'.config("global.upload_path").'products/';
       $location=$file_path = session('zip_full_path') ?? '';
       $file_pending = session('zip_pending') ?? 0;
       if ( ($file_pending > 0) && (file_exists($file_path) !== FALSE) ) {
         $zip = new ZipArchive;

              if($zip->open($location))
              {
                   $zip->extractTo($path);
                   $zip->close();
              }

            $all_files = glob("{$path}/*.*");
           $image_names = [];
           for ($i = 0; $i < count($all_files); $i++) {
               $image_name = $all_files[$i];
               $supported_format = array('jpg','jpeg','png');
               $basename = pathinfo($image_name, PATHINFO_BASENAME);
               $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
               if ( in_array($ext, $supported_format) ) {
                   $image_names[] =$basename;// [$image_name, $ext];
               }
           }



              if(!empty($image_names)){
              $products=DB::table('product_temp_image')->whereIn('image',$image_names)->get();
              if($products->isEmpty()){
                return response()->json(['success' => 0, 'message' => 'No Products found']);
              }
              $name = "";
              foreach($products as $row){
               copy($path.$row->image, $new_path . $row->image);
               unlink($path.$row->image);

                $res=ProductAttribute::where(['product_id'=>$row->product_id,'product_attribute_id'=>$row->product_attribute_id])->first();
                if($res){
                   $image=($res->image)?$res->image.','.$row->image:$row->image;
                   ProductAttribute::where(['product_id'=>$row->product_id,'product_attribute_id'=>$row->product_attribute_id])->update(['image'=>$image]);
                   $products=DB::table('product_temp_image')->where(['image'=>$row->image,'product_attribute_id'=>$row->product_attribute_id])->delete();
                }
           }
              unlink($location);
              $this->deleteDirectory($path . $name);
              $status = "1";
              $message = "Image uploaded successfully";
       }
     }else {
          if ( $file_pending > 0 ) {
              $message = 'Something went wrong. Please try again later.';
          } else {
              $message = 'The current file is already processed.';
          }
      }

        header('Content-type:application/json');
        echo json_encode(['status' => $status, 'message' => $message]);
    }

    function deleteDirectory($dir) {
      if (!file_exists($dir)) {
          return true;
      }

      if (!is_dir($dir)) {
          return unlink($dir);
      }

      foreach (scandir($dir) as $item) {
          if ($item == '.' || $item == '..') {
              continue;
          }

          if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
              return false;
          }

      }

      return rmdir($dir);
    }



    public function export_product(REQUEST $request)
    {

        $attr_col_start=21;
        $category= Categories::with('children')->where(['deleted'=>0,'active'=>1])->where('parent_id','=',0)->get();


         $vendors = VendorModel::with('stores')->select('users.id AS id', 'users.name as name')
         ->where('users.role', '3')
         ->get();

         $attribute = ProductModel:: getProductAttributes();
        $attribute_values = [];
         foreach ($attribute as $attr_val) {
             $attribute_values[$attr_val->attribute_id][$attr_val->attribute_values_id] = $attr_val->attribute_values;
         }

         foreach ($attribute_values as $attribute_id => $attr_val) {
             ksort($attr_val);
             $attribute_values[$attribute_id] = $attr_val;
         }
        $spreadsheet    = new Spreadsheet();
        $sheet1        = new Worksheet($spreadsheet, 'Products');
        $sheet2        = new Worksheet($spreadsheet, 'Categories');
        $sheet3        = new Worksheet($spreadsheet, 'Vendor');
        $sheet4        = new Worksheet($spreadsheet, 'Attributes');


        $sheet1->setCellValue('A1', 'Product Type');
        $sheet1->setCellValue('B1', 'Category(Multiselect)');
        $sheet1->setCellValue('C1', 'Default Category');
        $sheet1->setCellValue('D1', 'Product Name');
        $sheet1->setCellValue('E1', 'Vendor');
        $sheet1->setCellValue('F1', 'SKU');
        $sheet1->setCellValue('G1', 'regular Price');
        $sheet1->setCellValue('H1', 'Sale Price');
        $sheet1->setCellValue('I1', 'Stock Quantity');
        $sheet1->setCellValue('J1', 'Allow back Orders');
        $sheet1->setCellValue('K1', 'Barcode');
        $sheet1->setCellValue('L1', 'Product Code');
        $sheet1->setCellValue('M1', 'Short Description');
        $sheet1->setCellValue('N1', 'Full Description');
        $sheet1->setCellValue('O1', 'Meta Title');
        $sheet1->setCellValue('P1', 'Meta Keyword');
        $sheet1->setCellValue('Q1', 'Meta Description');
        $sheet1->setCellValue('R1', 'Other Spec Title(seperated by #)');
        $sheet1->setCellValue('S1', 'Other Spec Description(seperated by #)');
        $sheet1->setCellValue('T1', 'Images(seperated by comma)');


        $sheet2->setCellValue("A1", "Category");
        $sheet2->setCellValue("B1", "ID");

        $sheet3->setCellValue("A1", "Vendor");
        $sheet3->setCellValue("B1", "ID");


        $spreadsheet->addSheet($sheet1, 0);
        $spreadsheet->addSheet($sheet2, 1);
        $spreadsheet->addSheet($sheet3, 2);
        $spreadsheet->addSheet($sheet4, 3);


        $sheet2->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet3->getStyle('A1:B1')->getFont()->setBold(true);

        //category
        $brand_row = 2;
        $v=$b=$c=$p=[];
        foreach ($category as $row) {
            $sheet2->setCellValue("A".$brand_row, $row->name);
            $sheet2->setCellValue("B".$brand_row,  $row->id);
            $row->name=str_replace("'"," ",$row->name);
            $p[]= $row->name.'  =>'.$row->id;
            if(!empty($row->children)){
              foreach ($row->children as $child) {
                $p[]= '----'.$child->name.'  =>'.$row->id."#".$child->id;
                $brand_row++;
              }
            }else{
              $brand_row++;
            }

        }
        if ($brand_row > 2 ) {
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'CATEGORY_LIST',
                    $sheet2,
                    'A1:A'.($brand_row-1),
                    false,
                    NULL
                )
            );
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'CATEGORY_LIST_ALL',
                    $sheet2,
                    'A1:B'.($brand_row-1),
                    false,
                    NULL
                )
            );
        }

        //VENDOR
        $vendor_row = 2;
        foreach ($vendors as $row) {
            $sheet3->setCellValue("A".$vendor_row, $row->name);
            $sheet3->setCellValue("B".$vendor_row,  $row->id);
            $vendor_row++;
            if(!empty($vendor_row->stores)){
              foreach($vendor_row->stores as $storeKey){
                $row->name=str_replace("'"," ",$row->name);
                $v[]= $storeKey->store_name." (".$row->name.")".'#'.$storeKey->id;
              }
            }

        }
        if ($vendor_row > 2 ) {
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'VENDOR_LIST',
                    $sheet3,
                    'A1:A'.($vendor_row-1),
                    false,
                    NULL
                )
            );
            $spreadsheet->addNamedRange(
                new NamedRange(
                    'VENDOR_LIST_ALL',
                    $sheet3,
                    'A1:B'.($vendor_row-1),
                    false,
                    NULL
                )
            );
        }



        $sheet2->getProtection()->setSheet(true);
        $sheet3->getProtection()->setSheet(true);



        // Attribute List
        $attr_col = 0;
        $attr_val_label_col = 1;
        $attr_val_key_col = 2;

        $attributes=[];
          foreach ($attribute as $row) {
              $attributes[$row->attribute_id]=$row->attribute_name;
          }
        foreach ($attributes as $attribute_id => $attribute_name) {
            if ( array_key_exists($attribute_id, $attribute_values) === FALSE ) {
                continue;
            }

            $attr_slug = 'attr_'. url_title($attribute_name, '_', TRUE).'_k'.$attribute_id;

            $attr_col_label = Coordinate::stringFromColumnIndex($attr_col_start+$attr_col);
            $sheet1->setCellValue($attr_col_label.'1', 'Attribute '. $attribute_name);

            $attr_val_label_letter = Coordinate::stringFromColumnIndex($attr_val_label_col);
            $attr_val_key_letter   = Coordinate::stringFromColumnIndex($attr_val_key_col);

            $sheet4->setCellValue($attr_val_label_letter."1", $attribute_name);
            $sheet4->setCellValue($attr_val_key_letter."1", $attr_slug);

            $a_key = 0;
            foreach ($attribute_values[$attribute_id] as $attr_val_id => $attr_val_name) {
                $sheet4->setCellValue($attr_val_label_letter.($a_key+2), $attr_val_name);
                $sheet4->setCellValue($attr_val_key_letter.($a_key+2), 'attr_val_'.$attr_val_id);
                $a_key++;
            }

            $sheet4->getStyle($attr_val_label_letter.'1:'.$attr_val_key_letter.'1')->getFont()->setBold(true);

            if ($a_key >= 0) {
                $spreadsheet->addNamedRange(
                    new NamedRange(
                        $attr_slug,
                        $sheet4,
                        $attr_val_label_letter . '2:'.$attr_val_label_letter.($a_key+1),
                        false,
                        NULL
                    )
                );

            }

            $attr_val_label_col += 2;
            $attr_val_key_col += 2;

            $attr_col++;
        }



        $sheet1->getStyle('A1:'.$sheet1->getHighestDataColumn().'1')->getFont()->setBold(true);
        $spreadsheet->setActiveSheetIndex(0);


        // PRODUCT TYPE
        $sheet1->setDataValidation(
            'A2:A1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Product error')
                ->setError('Product type is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"Simple,Variable"'));
                // CATEGORY
        $sheet1->setDataValidation(
            'B2:B1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Category error')
                ->setError('Category is not in list.')
                ->setPromptTitle('Pick from list')
                 ->setFormula1(sprintf('"%s"',implode(',',$p)))

        );
        // DEFAULT CATEGORY
        $sheet1->setDataValidation(
            'C2:C1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Default category error')
                ->setError('Default Category is not in list.')
                ->setPromptTitle('Pick from list')
                 ->setFormula1(sprintf('"%s"',implode(',',$p)))

        );
        //brand
        $sheet1->setDataValidation(
            'E2:E1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Vendor error')
                ->setError('Vendor is not in list.')
                ->setPromptTitle('Pick from list')
                  ->setFormula1(sprintf('"%s"',implode(',',$v)))


        );

          // Regular Price, Sale Price & Stock
          $sheet1->setDataValidation(
            'G2:G1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_DECIMAL)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Regular Price Error')
                ->setOperator(DataValidation::OPERATOR_GREATERTHAN)
                ->setError('Value must be greater than zero.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1(0)
        );

        $sheet1->setDataValidation(
            'H2:H1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_DECIMAL)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Sale Price Error')
                ->setError('Value must be greater than zero and less than or equal to Regular Price.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1(0)
                ->setFormula2('G2')
        );

        // stock count
        $sheet1->setDataValidation(
            'I2:I1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_CUSTOM)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Stock Error')
                ->setError('Value must be a number.')
                ->setPromptTitle('Invalid Value')
                ->setFormula1('=ISNUMBER(I2)')
        );
        // allow back orders
       $sheet1->setDataValidation(
            'J2:J1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Vendor error')
                ->setError('Vendor is not in list.')
                ->setPromptTitle('Pick from list')
                  ->setFormula1('"YES,NO"')


        );

         // Attributes
        $attr_j = 0;
        $attribute_col_labels = [];
        foreach ($attributes as $attribute_id => $attribute_name) {
            if ( array_key_exists($attribute_id, $attribute_values) === FALSE ) {
                continue;
            }

            $attr_col_label = Coordinate::stringFromColumnIndex($attr_col_start+$attr_j);

            $attr = [];
            foreach ($attribute_values[$attribute_id] as $attr_val_id => $attr_val_name) {
               $attr[]      = $attr_val_id.'#'.$attr_val_name;
            }
            $attribute_col_labels[$attribute_id] = $attr_col_label;

            $spreadsheet->getActiveSheet()->setDataValidation(
                "{$attr_col_label}2:{$attr_col_label}1048576",
                (new DataValidation())
                    ->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_STOP )
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Attribute '. $attribute_name .': value error')
                    ->setError('Attribute '. $attribute_name .': value is not in list.')
                    ->setPromptTitle('Pick from list')
                    ->setFormula1(sprintf('"%s"',implode(',',$attr)))
            );

            $attr_j++;
        }

        if($request->get('blank') != 1){

        }

        $sheet1->getColumnDimension('A')->setWidth(40);
        $sheet1->getColumnDimension('B')->setWidth(40);
        $sheet1->getColumnDimension('C')->setWidth(40);
        $sheet1->getColumnDimension('D')->setWidth(30);
        $sheet1->getColumnDimension('E')->setWidth(40);
        $sheet1->getColumnDimension('F')->setWidth(40);
        $sheet1->getColumnDimension('G')->setWidth(40);
        $sheet1->getColumnDimension('H')->setWidth(30);
        $sheet1->getColumnDimension('I')->setWidth(30);
        $sheet1->getColumnDimension('J')->setWidth(40);
        $sheet1->getColumnDimension('K')->setWidth(40);
        $sheet1->getColumnDimension('L')->setWidth(40);
        $sheet1->getColumnDimension('M')->setWidth(40);
        $sheet1->getColumnDimension('N')->setWidth(30);
        $sheet1->getColumnDimension('O')->setWidth(40);
        $sheet1->getColumnDimension('P')->setWidth(30);
        $sheet1->getColumnDimension('Q')->setWidth(30);
        $sheet1->getColumnDimension('R')->setWidth(30);
        $sheet1->getColumnDimension('S')->setWidth(30);
        $sheet1->getColumnDimension('T')->setWidth(30);


        $date = date('d-m-y-'.substr((string)microtime(), 1, 8));
        $date = str_replace(".", "", $date);
        $filename = "product_list".$date.".xlsx";

        try {
            $writer = new Xlsx($spreadsheet);
            $writer->save($filename);
            $content = file_get_contents($filename);
        } catch(Exception $e) {
            exit($e->getMessage());
        }

        header("Content-Disposition: attachment; filename=".$filename);

        unlink($filename);
        exit($content);


    }
}
