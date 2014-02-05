<?php namespace Werkzeugh\TranslationAdmin;

use  BaseController,Redirect, View, Input, Response;
use L_DB as DB;


class TranslationAdminController extends BaseController {


  protected $layout = 'backend._layouts.default';
  protected $layoutdata = ['pageclass'=>'TranslationAdmin'];



  public function __construct(/*LanguageProvider $languageProvider, LanguageEntryProvider $languageEntryProvider*/)
  {
    $this->setProviders(\App::make('Werkzeugh\TranslationAdmin\LanguageProvider'), \App::make('Werkzeugh\TranslationAdmin\LanguageEntryProvider'));
  }


  protected $languageProvider;
  protected $languageEntryProvider;

  protected function setProviders($languageProvider, $languageEntryProvider)
  {
    $this->languageProvider       = $languageProvider;
    $this->languageEntryProvider  = $languageEntryProvider;
  }

  public function init()
  {

   \Orchestra\Asset::add('jquery'                    , "/Mwerkzeug/thirdparty/jquery/jquery-1.10.2.min.js");

   \Orchestra\Asset::add('angular'                   , "/mysite/thirdparty/ng/angular.min.js");
   \Orchestra\Asset::add('angular-animate'           , "/mysite/thirdparty/ng/angular-animate.min.js");

   \Orchestra\Asset::add('tinymce'                   , "/mysite/thirdparty/tinymce4/js/tinymce/tinymce.min.js");
   \Orchestra\Asset::add('ui-tinymce'                , "/mysite/thirdparty/ui-tinymce-0.0.4/src/tinymce.js");

   \Orchestra\Asset::add('werkzeugh-translate'       , "/packages/werkzeugh/translation-admin/ng/translation-admin/js/translation-admin.js");
   \Orchestra\Asset::add('werkzeugh-translate-index' , "/packages/werkzeugh/translation-admin/ng/translation-admin/directives/translation-admin-index.js");
   \Orchestra\Asset::add('werkzeugh-translate-item' , "/packages/werkzeugh/translation-admin/ng/translation-admin/directives/translation-admin-item.js");
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



public function getNgItems()
{
  return $this->postNgItems();
}


public function getAvailableLanguages()
{
  static $langs;
  if(!$langs)
  {
    foreach ($this->languageProvider->findAll() as $value) {
      $langs[$value->locale]=$value->getAttributes();
    }
  }
  return $langs;
}

public function getAvailableLocalesById()
{
  static $langsById;
  if(!$langsById)
  {
    foreach ($this->getAvailableLanguages() as $key=>$value) {
      $langsById[$value['id']]=$key;
    }
  }
  return $langsById;
}



public function postNgItemDetails()
{



  $ret['record1']= $this->languageEntryProvider->findById(Input::get('id1'));
  $ret['record2']= $this->languageEntryProvider->findById(Input::get('id2'));

  if($ret['record1'])
      $ret['record1']=$ret['record1']->getAttributes();
  if($ret['record2'])
      $ret['record2']=$ret['record2']->getAttributes();

  $ret['status']='ok';


  return Response::json($ret);




}


public function postNgSaveItem()
{

  if(Input::get('record1'))
    $this->saveRecord(Input::get('record1'));

  if(Input::get('record2'))
    $this->saveRecord(Input::get('record2'),Input::get('record1'));

  $ret['status']='ok';


  $id=Input::get('record1.id');
  $ret['item']=$this->postNgItems();

  return Response::json($ret);


}

public function saveRecord($record,$record1=NULL)
{

  if($record['id']>0)
  {
    $baserec= $this->languageEntryProvider->findById($record['id']);
  }
  elseif(!$record['id'] && $record['text'] && $record1)
  {

    $lang_id=$this->getAvailableLanguages()[Input::get('query.lang2')]['id'];
    // echo "make new rec for $lang_id";
    $baserec = $this->languageEntryProvider->createModel();
    $baserec->namespace = $record1['namespace'];
    $baserec->group = $record1['group'];
    $baserec->item = $record1['item'];
    $baserec->language_id =$lang_id;

  }
  else
    return FALSE;


    $baserec->text=$record['text'];
    $baserec->locked=1;
    $baserec->save();

    return TRUE;



}


public function postNgItems()
{

  $ret['query']=Input::get('query');
  if(Input::get('record1'))
    $single_id=Input::get('record1.id');

  // $GLOBALS['debugsql']=1;

  $ret['status']='ok';
  $ret['available_languages']=$this->getAvailableLanguages();
  $ret['items']=Array();

  $langs=$this->getAvailableLocalesById();
    $ret['available_languages_by_id']=$langs;

  $lang1_id=$ret['available_languages'][Input::get('query.lang1').""]['id'];
  $lang2_id=$ret['available_languages'][Input::get('query.lang2').""]['id'];

  $model = $this->languageEntryProvider->createModel();
  $ret['lang1_id']=$lang1_id;
  $ret['lang2_id']=$lang2_id;

  DB::connection()->setFetchMode(\PDO::FETCH_ASSOC);

  $query=DB::table($model->getTable());




 // $GLOBALS['debugsql']=1;


  $query->select([

    'language_entries.group as group',
    'language_entries.namespace as namespace',
    'language_entries.group as group',
    'language_entries.item as item',

    'language_entries.id as lang1_id',
    'language_entries.text as lang1_text',
    'language_entries.locked as lang1_locked',
    'language_entries.unstable as lang1_unstable',

    'language_entries2.id as lang2_id',
    'language_entries2.text as lang2_text',
    'language_entries2.locked as lang2_locked',
    'language_entries2.unstable as lang2_unstable',

    ]);

  $query->leftJoin('language_entries as language_entries2', function($join)  use ($lang2_id)
        {
          $join->on('language_entries.item', '=', 'language_entries2.item')
           ->on('language_entries.namespace'   , '=',  'language_entries2.namespace'  )
           ->on('language_entries.group'       , '=',  'language_entries2.group'      )
           ->on('language_entries.item'        , '=',  'language_entries2.item'       )
           ->where('language_entries2.language_id', '=',$lang2_id);
        });


  $query->where('language_entries.language_id', '=',$lang1_id);

  $query->whereIn('language_entries.group', ['texts']);

  if($single_id>0)
  {
    $query->where('language_entries.id','=',$single_id);
  }
  else
  {
    $keyword=trim(Input::get('query.keyword'));
    if(strlen($keyword)<3)
      $keyword="";

    if($keyword)
    {
      $query->whereRaw("( lower(language_entries.item) like lower(?) or lower(language_entries.text) like lower(?) or lower(language_entries2.text) like lower(?)  )",
        [
        '%'.$keyword.'%',
        '%'.$keyword.'%',
        '%'.$keyword.'%',
        ]);
    }

  }

// $GLOBALS['debugsql']=1;



  $items=$query->take(100)->get();


  //fetch items in first language
  foreach ($items as $val) {
    $val['lang1_text']=str_limit(strip_tags($val['lang1_text']));
    $val['lang2_text']=str_limit(strip_tags($val['lang2_text']));

    if(preg_match('#(html|texts)#',$val['group']))
      $val['type']='html';
    elseif(preg_match('#(reminders)#',$val['group']))
      $val['type']='multiline';
    else
      $val['type']='text';
    $ret['items'][]=$val;
  }

 if($single_id)
  {

    return array_shift($ret['items']);

  }

  return Response::json($ret);

}


}
