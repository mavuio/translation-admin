<?php namespace Werkzeugh\TranslationAdmin;

use BaseController,Redirect, View, Input, Response;
use Illuminate\Support\Facades\DB;
use EcoAsset;

class TranslationAdminController extends \BackendBaseControllerForPackages {


  protected $languageProvider;
  protected $languageEntryProvider;

  protected function setProviders($languageProvider, $languageEntryProvider)
  {
    $this->languageProvider       = $languageProvider;
    $this->languageEntryProvider  = $languageEntryProvider;
  }

  public function init()
  {
      parent::init();
      $this->setProviders(\App::make('Werkzeugh\TranslationAdmin\LanguageProvider'), \App::make('Werkzeugh\TranslationAdmin\LanguageEntryProvider'));
  

       // assume jquery and angular are loaded
       // EcoAsset::add('jquery'                    , "/Mwerkzeug/thirdparty/jquery/jquery-1.10.2.min.js");
       // 
       // EcoAsset::add('angular'                   , "/mysite/thirdparty/ng/angular.min.js");
       // EcoAsset::add('angular-animate'           , "/mysite/thirdparty/ng/angular-animate.min.js");
       
       //TODO remove mysite thirdparty-references
       EcoAsset::add('tinymce'                   , "/packages/werkzeugh/translation-admin/thirdparty/tinymce4/js/tinymce/tinymce.min.js");
       EcoAsset::add('ui-tinymce'                , "/packages/werkzeugh/translation-admin/thirdparty/ui-tinymce-0.0.4/src/tinymce.js");

       EcoAsset::add('werkzeugh-translate'       , "/packages/werkzeugh/translation-admin/ng/translation-admin/js/translation-admin.js");
       EcoAsset::add('werkzeugh-translate-index' , "/packages/werkzeugh/translation-admin/ng/translation-admin/directives/translation-admin-index.js");
       EcoAsset::add('werkzeugh-translate-item' , "/packages/werkzeugh/translation-admin/ng/translation-admin/directives/translation-admin-item.js");
       EcoAsset::add('werkzeugh-translate-css'   , "/packages/werkzeugh/translation-admin/css/translation-admin.css");

  }

 public function getIndex()
 {

   $c=[];
  
   $this->eco->setContentTemplate('mypkg::translation-admin-index');

   $c['settingsAsJson']=json_encode($this->getSettings());
   return $c;

 }

 public function getSettings()
 {
    $ret=[];     
    $ret['baseUrl']=\URL::current();
    if (preg_match('/([a-z][a-z]),([a-z][a-z])/', Input::get('langs'),$m)) {
      $ret['query']['lang1']=$m[1];
      $ret['query']['lang2']=$m[2];
    }
    return $ret;
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

    $newtext=$this->cleanupTextFromEditor($record['text']);

    if($newtext<>$baserec->text)
        $baserec->locked=1;

    $baserec->text=$newtext;
    $baserec->save();

    return TRUE;

}

  public function cleanupTextFromEditor($txt)
  {

    return $txt;
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

    $category=trim(Input::get('query.category'));
    if($category)
    {
        if(!strstr($category,'.'))
            $category='*.'.$category;

        list($namespace,$group)=explode('.',$category);

        if($group)
        {
            $query->where('language_entries.group','=',$group);
        }

        if($namespace)
        {
            $query->where('language_entries.namespace','=',$namespace);
        }

    }


  }

// $GLOBALS['debugsql']=1;



  $items=$query->take(100)->get();


  //fetch items in first language
  foreach ($items as $val) {
    $val['lang1_text']=str_limit(strip_tags($val['lang1_text']));
    $val['lang2_text']=str_limit(strip_tags($val['lang2_text']));

    if(preg_match('#(html|text)#',$val['group'].$val['item']))
      $val['type']='html';
    elseif(preg_match('#(multiline)#',$val['group'].$val['item']))
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


public function postNgGroups()
{
    $model = $this->languageEntryProvider->createModel();

    $query=DB::table($model->getTable());

    $lang1_id=$this->getAvailableLanguages()[Input::get('query.lang1').""]['id'];


    DB::connection()->setFetchMode(\PDO::FETCH_ASSOC);

    $query->select(DB::raw('`namespace`,`group`,count(item) as `count`'))->where('language_id','=',$lang1_id)->groupBy('namespace','group');

    foreach ($query->get() as $row) {
        if($row['namespace'] && $row['namespace']!='*')
            $row['combined_name']="$row[namespace].$row[group]";
        else
            $row['combined_name']="$row[group]";

        $ret['items'][]=$row;
    }

    $ret['status']='ok';


    return Response::json($ret);
}

}
