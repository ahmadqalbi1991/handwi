<?php

namespace App\Http\Controllers\Admin;

use App\Models\FaqModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_heading = "FAQ";
        $faqs = FaqModel::get();

        return view("admin.faq.index", compact("page_heading", "faqs"));
    }

    public function create($id = null)
    {
        $page_heading = "Create FAQ";
        $content = null;
        if ($id) {
            $content = FaqModel::find($id);
        }

        return view('admin.faq.create', compact('page_heading', 'content'));
    }

    public function save(Request $request) {
        $input = $request->all();
        if (empty($input['faq_id'])) {
            FaqModel::create($input);
        } else {
            FaqModel::where('faq_id', $input['faq_id'])->update($input);
        }

        return return_response('1', 200, 'FAQ saved successfully');
    }

    public function delete($id)
    {
        FaqModel::where('faq_id', $id)->delete();

        return return_response('1', 200, 'FAQ deleted');
    }

    public function changeStatus(Request $request)
    {
        try {
            $campaign = FaqModel::find($request->id);
            if ($campaign) {
                $campaign->status = (bool)$request->status;
                $campaign->save();

                return return_response('1', 200, 'Status changed successfully');
            } else {
                return return_response('0', 404, 'FAQ not found');
            }
        } catch (\Exception $exception) {
            return return_response('0', 500,  'Something went wrong');
        }
    }
}
