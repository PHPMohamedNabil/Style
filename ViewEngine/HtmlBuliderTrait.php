<?php

namespace App\Core\ViewEngine;


trait HtmlBuliderTrait{
      
    public static $formIn=[];

    public static $htmlForm;

    protected $html_file;
        

             /**
              * store css_file you selected to create 
              *@var obj
               */
    protected $cssfile;

           /**
            * store js_file you selected to create 
            *@var obj
            */
    

    protected $jsfile;

   
   public static function form($action,$attr)
   {
         $form = '<form action="'.$action.'" ';
         foreach ($attr as $key=>$value)
         {
               $form .=' '.$key.'="'.$value.'" ';
         }

         $form.='>';
         //$inputs ='';
         self::$htmlForm =$form;

         return (new self);

   }

   public  function formInput($name,$attr)
   {
        if(!isset(self::$formIn[$name]))
         self::$formIn[$name]=$attr;
        return $this;
        
   }

   public  function renderForm()
   {
        $form=self::$htmlForm;
        if(count(self::$formIn)>0)
         {
            //dd(self::$formIn);
           
            foreach(self::$formIn as $key =>$value)
            { 
               $form.="\r\n\n\t\t".'<input ';
               $form .='name="'.$key.'"'.' ';
               foreach($value as $attr =>$val)
               {
                    $form.=' '.$attr.'="'.$val.'"';
               }
               $form.=' />';
            }

         }

         $form .="\r\n\n\t".'</form>';

         return $form;
   }


   function editeHtml($file)
   {
       $this->html_file=$file;
       $this->settings['edit_html']=true;
       
        return $this->html_file;

   }
   
   function createHeadElements($file='')
   {
      
      $file_path         = $file;

      $this->html_file   = $file;
 

      $head='<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title></title>
</head>';
      
      $head.="<body>\n";

      $head.="</html>\n";
      $head.="</body>\n";
    
        //dd($head);
     
       if($file)
       {
         return file_put_contents($file_path,$head,FILE_APPEND);
       }
      
      return $head;
      
    

     }
   
   
   function createStyleFile($file,$style_folder=null,$content=null)
   {
     $style_folder=(($style_folder != null))?$style_folder.'/':null;
     
     file_put_contents($this->tempdir.$style_folder.$file.'.'.'css',$content);

   }
   
   function createScriptFile($file,$js_folder,$content)
   {
    
    $js_folder=(($style_folder != null))?$js_folder.'/':null;
    
    file_put_contents($this->tempdir.$js_folder.$file.'.'.'js',$content);

   }
   

   function set_cssfile_path($cssfile,$folder=null)
   {
     
     $folder=(($folder != null))?$folder.'/':null;

     
     $this->cssfile=$this->tempdir.$folder.$cssfile.'.'.'css';



   }
   
   function set_jsfile_path($jsfile,$folder=null)
   {
     $folder=(($folder != null))?$folder.'/':null;

     $this->jsfile=$this->tempdir.$jsfile;

   }

   function set_responsive($done=true)
   {
      $cssfile=$this->cssfile;
       
       if (!$done) {
          
          return null;
       }
    
     $media='* {
    box-sizing: border-box;
}

img{
  max-width: 100%;
    height: auto;
}

video{
  max-width: 100%;
  height: auto;
}
.row::after {
    content: "";
    clear: both;
    display: table;
}
[class*="col-"] {
    float: left;
    padding: 15px;
}

/* For mobile phones: */
[class*="col-"] {
    width: 100%;
}
@media only screen and (min-width: 480px) {
    /* For tablets: */
    .col-m-1 {width: 8.33%;}
    .col-m-2 {width: 16.66%;}
    .col-m-3 {width: 25%;}
    .col-m-4 {width: 33.33%;}
    .col-m-5 {width: 41.66%;}
    .col-m-6 {width: 50%;}
    .col-m-7 {width: 58.33%;}
    .col-m-8 {width: 66.66%;}
    .col-m-9 {width: 75%;}
    .col-m-10 {width: 83.33%;}
    .col-m-11 {width: 91.66%;}
    .col-m-12 {width: 100%;}
}
@media only screen and (min-width: 768px) {
    /* For desktop: */
    .col-1 {width: 8.33%;}
    .col-2 {width: 16.66%;}
    .col-3 {width: 25%;}
    .col-4 {width: 33.33%;}
    .col-5 {width: 41.66%;}
    .col-6 {width: 50%;}
    .col-7 {width: 58.33%;}
    .col-8 {width: 66.66%;}
    .col-9 {width: 75%;}
    .col-10 {width: 83.33%;}
    .col-11 {width: 91.66%;}
    .col-12 {width: 100%;}
}';
    file_put_contents($cssfile,$media,FILE_APPEND);
    

        
       
      
   }
    
    

   function removeHtml($html)
   {
     $html=$this->tempdir.$html;
     
     if (file_exists($html))
     {
        unlink($html);
         unset($this->html);
     }

     else
     {
      return false;
     }
    

   }

   function createHtml($html_file,$folder=null)
   {
       
       $this->html_file=$html_file;
       
      $temp_path=(($folder === null))?$this->tempdir:$this->tempdir.$folder.DIRECTORY_SEPARATOR;

      
         $file = $temp_path.$html_file.'.stl.'.$this->tempex;

      //dd($file);
     
     if ($folder)
     {
     
        mkdir($temp_path,0777,true);
     
     }
     if(!file_exists($file))
     {
        
      if (!is_writable($temp_path))
      {
       
       $this->addError('access_to_write','you dont have access to create new folder on the server please check a write permissinos on the server');
       
       $this->print_error('access_to_write');
      
      }
          file_put_contents($file,'');
     return $this->createHeadElements($file);
    
    }
    return null;

   } 
   
   
}