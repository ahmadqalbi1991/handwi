<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductModel;
use App\Models\ProductMasterModel;
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

class ProductMasterImport extends Controller
{
    //
    public function importproductmaster(){
      $page_heading= "Master Product Import";
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
      return view('admin.product_master.master_product_import',compact('companies','stores','id','store_id','_current_user','category_list','sub_category_list','category_ids','page_heading'));
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

        //ob_flush();
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
    public function master_pd_upload_file(REQUEST $request)
    {

      $path1 = $request->file('file')->store('temp');
      $path=storage_path('app').'/'.$path1;
      $request->session()->put(['xlsx_full_path'=>$path,'xlsx_pending'=>1]);
      //$_SESSION['xlsx_full_path'] = $path;
      //$_SESSION['xlsx_pending']   = 1;
      echo $path;
    }
    public function start_master_import(REQUEST $request){

      $success = 0;
      $failed = 0;
      $i=0;
       header('Content-Type: text/event-stream');
      //   // recommended to prevent caching of event data.
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
             
                 


           foreach($data[0] as $key => $row)
           {
            if($key != 0)
            {
  
            
            $flag=0;

             
          
            $insert_data =  [
                    'name' => $row[0],
                    'active' => ($row[1]=="Active")?1:0,
                    'created_at' => gmdate('Y-m-d H:i:s'),
                    'updated_at' => gmdate('Y-m-d H:i:s'),
            ];

              if(!empty($insert_data))
              {
                $status = 0;
                $check = ProductMasterModel::where(['name'=>$row[0]])->get()->count();
                if($check == 0)
                {
                $status = ProductMasterModel::insertGetId($insert_data);
                }
               



                     if ( $status ) {
                         $success++;
                     } else {
                         $failed++;
                     }

                     $this->send_message($i, 'on iteration ' . $i . ' of '.$totalRows, $i, $totalRows, $success, $failed);
                     $i++;
              }
              
             }
                  //end
           }
           $_SESSION['xlsx_pending'] = 0;
            @unlink($file_path);
            $this->send_message('CLOSE', 'Process complete', $totalRows, $totalRows, $success, $failed);

            return response()->json(['success' => 1, 'message' => 'Excel Data Imported successfully.']);
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



    
    public function download_masterproduct_format()
    {

        $spreadsheet    = new Spreadsheet();
        $sheet1        = new Worksheet($spreadsheet, 'Products');

        
        $sheet1->setCellValue('A1', 'Product Name');
        $sheet1->setCellValue('B1', 'Status');
       

    
        $spreadsheet->addSheet($sheet1, 0);
        $sheet1->getStyle('B1:'.$sheet1->getHighestDataColumn().'1')->getFont()->setBold(true);
        $spreadsheet->setActiveSheetIndex(0);

        // PRODUCT STATUS
        $sheet1->setDataValidation(
            'B2:B1000',
            (new DataValidation())
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP )
                ->setAllowBlank(false)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setShowDropDown(true)
                ->setErrorTitle('Product error')
                ->setError('Status is not in list.')
                ->setPromptTitle('Pick from list')
                ->setFormula1('"Active,Inactive"'));
        
             

        $sheet1->getColumnDimension('A')->setWidth(40);
        $sheet1->getColumnDimension('B')->setWidth(40);
       

        $date = date('d-m-y-'.substr((string)microtime(), 1, 8));
        $date = str_replace(".", "", $date);
        $filename = "master_product_list".$date.".xlsx";

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
