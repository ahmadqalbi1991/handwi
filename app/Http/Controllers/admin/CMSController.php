<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CMS;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CMSController extends Controller
{
    public function index() {
        $data['page_heading'] = 'CMS Pages';
        $data['cms_contents'] = CMS::get();

        return view('admin.cms.index')->with($data);
    }

    public function create($id = null) {
        $data['page_heading'] = 'CMS Page Create';
        if ($id) {
            $data['content'] = CMS::find($id);
        }

        return view('admin.cms.create')->with($data);
    }

    public function save(Request $request) {
        $input = $request->all();
        if (empty($input['id'])) {
            $input['slug'] = Str::slug($input['title']);
            $exists = CMS::where('slug', $input['slug'])->first();
            if ($exists) {
                return return_response('0', 200, 'CMS page already created');
            }
            CMS::create($input);
        } else {
            CMS::where('id', $input['id'])->update($input);
        }

        return return_response('1', 200, 'CMS page saved successfully');
    }

    public function changeStatus(Request $request)
    {
        try {
            $campaign = CMS::find($request->id);
            if ($campaign) {
                $campaign->status = (bool)$request->status;
                $campaign->save();

                return return_response('1', 200, 'Status changed successfully');
            } else {
                return return_response('0', 404, 'Campaign not found');
            }
        } catch (\Exception $exception) {
            return return_response('0', 500, 'Something went wrong');
        }
    }

    public function delete($id)
    {
        try {
            CMS::where('id', $id)->delete();

            return return_response('1', 200, 'CMS page deleted');
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error($exception);
            return return_response('0', 500, 'Something went wrong');
        }
    }
}
