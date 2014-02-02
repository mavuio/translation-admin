<?php namespace Werkzeugh\TranslationAdmin;

use  BaseController,Redirect, View, Input, Response;
use L_DB as DB;

class TranslationAdminController extends BaseController {


  protected $layout = 'backend._layouts.default';
  protected $layoutdata = ['pageclass'=>'TranslationAdmin'];


  public function init()
  {
   $base_path=public_path();

   \Orchestra\Asset::add('jquery'                    , $base_path."/Mwerkzeug/thirdparty/jquery/jquery-1.10.2.min.js");

   \Orchestra\Asset::add('angular'                   , $base_path."/mysite/thirdparty/ng/angular.min.js");
   \Orchestra\Asset::add('angular-animate'           , $base_path."/mysite/thirdparty/ng/angular-animate.min.js");

   \Orchestra\Asset::add('tinymce'                   , $base_path."/mysite/thirdparty/tinymce4/js/tinymce/tinymce.min.js");
   \Orchestra\Asset::add('ui-tinymce'                , $base_path."/mysite/thirdparty/ui-tinymce-0.0.4/src/tinymce.js");

   \Orchestra\Asset::add('werkzeugh-translate'       , $base_path."/packages/werkzeugh/translation-admin/ng/translation-admin/js/translation-admin.js");
   \Orchestra\Asset::add('werkzeugh-translate-index' , $base_path."/packages/werkzeugh/translation-admin/ng/translation-admin/directives/translation-admin-index.js");
   \Orchestra\Asset::add('werkzeugh-translate-css'   , $base_path."/packages/werkzeugh/translation-admin/css/translation-admin.css");


 }

 public function getIndex()
 {
  $this->init();

        //needed directives:
        // \Orchestra\Asset::add('wien-import-queue',"/../mysite/ng/wien-import/directives/wien-import-queue.js");
        // \Orchestra\Asset::add('wien-import-item',"/../mysite/ng/wien-import/directives/wien-import-item.js");

  $this->layout->page_classname='TranslationAdminPage';
  $this->layout->content = View::make('mypkg::translation-admin-index')->with('data', $data);


}

}
