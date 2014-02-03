<?php namespace Werkzeugh\TranslationAdmin;

use  BaseController,Redirect, View, Input, Response;
use L_DB as DB;

class TranslationAdminController extends BaseController {


  protected $layout = 'backend._layouts.default';
  protected $layoutdata = ['pageclass'=>'TranslationAdmin'];


  public function init()
  {

   \Orchestra\Asset::add('jquery'                    , "/Mwerkzeug/thirdparty/jquery/jquery-1.10.2.min.js");

   \Orchestra\Asset::add('angular'                   , "/mysite/thirdparty/ng/angular.min.js");
   \Orchestra\Asset::add('angular-animate'           , "/mysite/thirdparty/ng/angular-animate.min.js");

   \Orchestra\Asset::add('tinymce'                   , "/mysite/thirdparty/tinymce4/js/tinymce/tinymce.min.js");
   \Orchestra\Asset::add('ui-tinymce'                , "/mysite/thirdparty/ui-tinymce-0.0.4/src/tinymce.js");

   \Orchestra\Asset::add('werkzeugh-translate'       , "/packages/werkzeugh/translation-admin/ng/translation-admin/js/translation-admin.js");
   \Orchestra\Asset::add('werkzeugh-translate-index' , "/packages/werkzeugh/translation-admin/ng/translation-admin/directives/translation-admin-index.js");
   \Orchestra\Asset::add('werkzeugh-translate-css'   , "/packages/werkzeugh/translation-admin/css/translation-admin.css");


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
