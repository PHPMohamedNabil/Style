<?php

/**
 *  styleengine
 *  -------
 *  Realized by Nabil
 *  Distributed under MIT License 
 *  @version 1.0.0
 */

class StyleEngine{
        
        //-----------------------
        
           // Main configuration 
        
        //-----------------------




        /**
        *Template Base Path
        *
        *@var string
        */
           

            public  $tempdir='views/templates/';
        



        /**
        *Template files Extention
        *
        *@var string
        */ 
            
            private $tempex='html';

        /**
        *cache expire time set after how many times cache will be deleted 
        *
        *@var int
        */ 
      
            private $cache_exptime=3600;
        




      
       /**
       * errors array contains all errors  
       *
       *@var array
       */



        public static $errors=[];



       /**
       * settings array contains info about temps , files, and orders in prosecss 
       * @var arra
       */

      
        public $settings=[];


      /**
      *cache path where to keep the compiled cache files 
      *@var 
      */


        private $cache_dir='views/temp/';



      /**
      * array where StyleEngine store the variabls assined
      *@var arr
      */




        public  $vars=[];





       /**
      * store htm_file you selected to create 
      *@var obj
      */



        private $html_file;
        

             /**
              * store css_file you selected to create 
              *@var obj
               */


        
        private $cssfile;
        

           /**
            * store js_file you selected to create 
            *@var obj
            */
        




        private $jsfile;
        
        



     /**
      * enable php code in templates 
      *true: php code is enabled and can be written
      *false: php code wn'ot be compiled and ignore it
      *@var  boolean  
      */
        

           public  $enable_php=false;
        

     
     /**
      * enable check if cache file is expired or not  
      *if expired it will be deleted
      *@var  boolean  
      */

        

          public $check_cache_file=true;


   /**
   * assigning template variables
   * eg.  $t->assign('name','andrew');
   *@param mixed $name set the name you will use to print var into template. or set an assoc array dirctely
   *@param mixed $value value of var (you can pass an array). but not set if the name is an assoc array 

   */
    public function assign($name,$value=null)
    {
       
      if (is_array($name))
      {
       
       $this->vars += $name;
 
      }
      else
      {
       
       $this->vars[$name]=$value;
      
      }
    
    }
  
  /**
   * rendering template
   * eg. $tpl->show( 'homepage');
   * 
   * @param string $tpl_name  template to load
   */
    public function show($template_name)
    {
         // checks if it has to compile the template ot not 
         
         $this->check_template($template_name);

         
         
            extract($this->vars);
         
         include ($this->settings['compiled_file_name']);
    
    }
   
     /**
   * check cahce files if expired delete it 
   *@param string name of compield template
   *@return string compiled file name
   */
     protected function cache($compiled_file_name)
     {
      
      if ($this->check_cache_file && file_exists($compiled_file_name))
      {
         
      if ((time() - filemtime($compiled_file_name)) >= $this->cache_exptime)
      {
            
           unlink($compiled_file_name);
          
          return true;           
      }
      
      if (!is_writable($compiled_file_name))
      {
          $this->addError('access_to_delete_cache','you dont have access form the server to do this delete operation.check server support or set check_cache_file=false in StyleEngine file');
          $this->print_error('access_to_delete_cache');
      }


      }
       
       return $this->settings['compiled_file_name']=$compiled_file_name; 
    
     }
     
     //checks if has to compile template or not 
     // return true if template changed
     //return null when no changes did on template 

     protected function check_template($tpl_name)
     {   
 
        $tpl_path=$this->style_GetTemp($tpl_name,true);

        $compiled_file_name=$this->cache_dir.$tpl_name.'.'.md5($tpl_path).'.st'.'.php';
        
        $this->cache($compiled_file_name); //check cache file is expired or not 
        
        // file doesn't exsist, or the template was updated, styleengine will compile the template

       if(!file_exists($tpl_path) )
       {
          $this->addError('FileNotFound','Template'.' '.'<b>'.$tpl_name.'</b>'.' Not Found Please Check template name');

          $this->print_error('FileNotFound');
          
       }

      if( !file_exists( $compiled_file_name ) || filemtime($compiled_file_name) < filemtime( $tpl_path )  )
      { 
        $this->compile_File($tpl_name,$compiled_file_name);

        return true;
      }
       
       return null;
    }

    /**
    * arrang compiled  template file 
    * @access protected
    */
    
    protected function compile_File($template_full_path,$compiled_file_name)
    {   
        // get template file 

        $temp=$this->style_GetTemp($template_full_path);
           
           //checks if not found cache folder recreate it 
           if(!is_dir( $this->cache_dir ))
           {
             mkdir($this->cache_dir, 0755, true );
           }
            // compile template process
            $compile=$this->compile_template($temp);
            // remove new line shack after php tags 
          
            $compile = str_replace( "?>\n", "?>\n\n",$compile);
            

            $compiled_file=file_put_contents($compiled_file_name,$compile);
            
          // set copmile file name

        $this->settings['compiled_file_name']=$compiled_file_name;
            
             
    }
    /** 
    *compile template
    *@access protected
    */

    protected function compile_template($template_full_path)
    {
        // get the template path

      $template=$template_full_path;
      
      // array with full regx
      
      $exps=array('vars'        =>'{\$(\w+[^}*])}',
                  'loop'        =>'{%loop\s(\w+)%}',
                  'endloop'     =>'{%endloop%}',
                  'inside_loop'=>'{\$(\w+)\.?(\w+)?}',
                  'include_file'=>'{%display\=\"(.*)\"%}',
                  'function'    =>'{%func\s(echo+|)\s?(\w+)\(([^%]*)\)%}',
                  'if'          =>'{%if\s([^f]*)\%\}',
                  'endif'       =>'{%endif%}',
                  'php_start'   =>'\[php\]',
                  'php_end'     =>'\[\/php\]',
                  'cons'        =>'{\#(.*)}',
                  'var_declare' =>'{\$(\w+)\=(.*)}',
                  'elseif'      =>'{%elseif\s([^\f]*)\%\}',
                  'else'        =>'{%else="([^\f]*)"%}',
                  'comment'     =>'\[comment\]([^\f]*)\[\/comment\]',
                  'disable_comp' =>'\[no_compile\]([^\f]*)\[\/no_compile\]'
                 );
       
       
       // disable php tags 
      $temp=(($this->enable_php === false))?str_replace(['<?','?>'],['&lt;?','?&gt;'],$template):$template;
      

      $code_replace=preg_replace_callback('#'.$exps['loop'].'#', array($this, 'loops_Compile'),$temp);
       
      $code_replace=preg_replace_callback('#'.$exps['vars'].'#', array($this, 'vars_Compile'),$code_replace);
      
      $code_replace=preg_replace_callback('#'.$exps['inside_loop'].'#', array($this, 'inside_loop_compile'),$code_replace);
        
      $code_replace=preg_replace_callback('#'.$exps['endloop'].'#',array($this, 'loops_End_Compile'),$code_replace);
    
    
      $code_replace=preg_replace_callback('#'.$exps['include_file'].'#', array($this, 'compile_Get_File'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['function'].'#',array($this, 'compileFunctions'),$code_replace);
      
      $code_replace=preg_replace_callback('#'.$exps['if'].'#',array($this, 'compile_if_cond'),$code_replace);
      
      $code_replace=preg_replace_callback('#'.$exps['endif'].'#',array($this, 'compile_endif_cond'),$code_replace);
      
      $code_replace=preg_replace_callback('#'.$exps['elseif'].'#',array($this, 'else_if_Compile'),$code_replace);
      
      $code_replace=preg_replace_callback('#'.$exps['else'].'#',array($this, 'else__Compile'),$code_replace);
         
      $code_replace=preg_replace_callback('#'.$exps['comment'].'#',array($this, 'comment_tag_Compile'),$code_replace);
      
      $code_replace=preg_replace_callback('#'.$exps['php_start'].'#',array($this,'compile_php_start'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['php_end'].'#',array($this,'compile_php_end'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['cons'].'#',array($this,'compile_cons'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['var_declare'].'#',array($this,'var_declarer'),$code_replace);  

    
    /**  
    * preg_replace_callback_array works on php 7.0 or above versions

    $code_replace=preg_replace_callback_array(
      [
       '#'.$exps['loop'].'#'=>array($this, 'loops_Compile'),
       '#'.$exps['inside_loop'].'#'=>array($this, 'inside_loop_compile'),
       '#'.$exps['vars'].'#'=>array($this,'vars_Compile'),
       '#'.$exps['endloop'].'#'=>array($this, 'loops_End_Compile'),
       '#'.$exps['include_file'].'#'=>array($this, 'compile_Get_File'),
       '#'.$exps['function'].'#'=>array($this, 'compileFunctions'),
       '#'.$exps['if'].'#'=>array($this, 'compile_if_cond'),
       '#'.$exps['endif'].'#'=>array($this, 'compile_endif_cond'),
       '#'.$exps['php_start'].'#'=>array($this,'compile_php_start'),
       '#'.$exps['php_end'].'#'=>array($this,'compile_php_end')
     ],$temp);
   */
        return $code_replace;
    }

    // compile variables inside conds and func params
    
    function compile_vars_inside($code)
    {
      
      return preg_replace('/{\@(\w+)}/','$'.'$1',$code); 

    }
    // compile constans inside conds and func params
    
    function compile_cons_inside($code)
    {
      return preg_replace('/{\#(\w+)\#}/','$1',$code);  

    }
     
     // compile constance

    function compile_cons($capt)
    {

      return '<?php echo'.' '.$capt[1].' ?>';

    }
    
    function var_declarer($capt)
    {
      $value=$this->compile_vars_inside($capt[2]);
      
      $value=$this->compile_cons_inside($value);
      
      return '<?php $'.$capt[1].'="'.$value.'"; ?>';

    }

    function inside_loop_compile($capt)
    {
      $full_loop_word='';
      
      if ($capt[1] =='value' && count($capt)==3)
      {
        $full_loop_word .='<?php echo'.' '.'$value'.'["'.$capt[2].'"]'.';?>';
      }

      elseif ($capt[1]=='key')
      {
        $value=((isset($capt[2])) && $capt[2]=='value')?'.$'.$capt[2].'':null;

        $full_loop_word .='<?php echo'.' '.'$'.$capt[1].''.$value.';?>';            
      }
           
          return $full_loop_word;
    }
    
    function compileFunctions($capt)
    {  
      $phpright='<?php';
      
      $phpleft=';?>';

      $params=$this->compile_vars_inside($capt[3]);
      
      $params=$this->compile_cons_inside($params);
        
        return $phpright.' '.$capt[1].' '.$capt[2].'('.$params.')'.' '.$phpleft;

    }
    
    function loops_Compile($capt)
    {   
        return '<?php if( isset($'.$capt[1].') && is_array($'.$capt[1].') && sizeof($'.$capt[1].') ) foreach($'.$capt[1].' as $key=>$value){?>';
    }

    function loops_End_Compile($capt)
    {
        return '<?php  }  ?>';

    }

    public function vars_Compile($capt)
    { 
       return '<?php echo'.' '.'htmlentities($'.$capt[1].',ENT_QUOTES | ENT_IGNORE,"UTF-8");?>';

    }
    
    function style_GetTemp($tempname,$getpath=false)
    {
      if ($getpath)
      {
       return $this->tempdir.$tempname.'.'.$this->tempex;
      }
       return file_get_contents($this->tempdir.$tempname.'.'.$this->tempex);
      
      
    }
 
    function compile_Get_File($capt)
    {
      $file=$this->compile_vars_inside($capt[1]);
      
      $file=$this->compile_cons_inside($file);
     
     return'<?php $stl = new '.get_class($this).';'.' '.'$stl->assign($this->vars);$stl->show( basename("'.$file.'"));?>';

    }
    
    function compile_php_start($capt)
    {
      return '<?php';

    }

    function compile_php_end($capt)
    {
      return '?>';

    }

    function compile_if_cond($capt)
    { 
      $condition=$this->compile_vars_inside($capt[1]);

      $condition=$this->compile_cons_inside($condition);
        
     return $compiled_code="<?php if($condition){?>";
    }

    function compile_endif_cond($capt)
    {
        
 
            return "<?php   }   ?>";

    
    }

    function comment_tag_Compile($capt)
    {    
        $capt[0]=null;

        return $capt[0];

    }

    function else_if_Compile($capt)
    {
       $condition=$this->compile_vars_inside($capt[1]);

       $condition=$this->compile_cons_inside($condition);
        
        return "<?php }elseif($condition) { ?>";

    }

    function else__Compile($capt)
    {    
       $condition=$this->compile_vars_inside($capt[1]);
       $condition=$this->compile_cons_inside($condition);
      
        return '<?php }else{ ?>'.$condition.'<?php    }  ?>';

    }


    


    function change_template_folder($folder_name,$createfolder=false)
    {  
      $this->tempdir.=$folder_name;

      if ($createfolder && !is_dir($this->tempdir))
      {
         
         mkdir($this->tempdir,0777,true);




         
      if(!is_writable($this->tempdir))
      {

        $this->addError('access_to_write','you dont have access to create new folder on the server please check a write permissinos');

        $this->print_error('access_to_write');

      }

      }
     
     }
    
   function createHtml($html_file,$folder=null)
   {
       
       $this->html_file=$html_file;
       
       $foldertemp=(($folder === null))?$this->tempdir:$folder;
       
       $temp_path=$this->tempdir.$foldertemp.'/'.$this->html_file;

     
     if (!is_dir($temp_path))
     {
     
       mkdir($temp_path,0777,true);
     
     }
        
      if (!is_writable($temp_path))
      {
       
       addError('access_to_write','you dont have access to create new folder on the server please check a write permissinos on the server');
       
       print_error('access_to_write');
      
      }
      
       file_put_contents($temp_path.'.'.$this->tempex,'');
    
    } 

   function editeHtml($file)
   {
       $this->html_file=$file;
       $this->settings['edit_html']=true;
       
        return $this->html_file;

   }
   
   function createHeadElements($tags=[],$conditions=null)
   {
      
      $file_path         =$this->style_GetTemp($this->html_file,true);

      $file_content      =$this->style_GetTemp($this->html_file);

      $head              ='';
      
      $append            ='';
      
      if (!is_array($conditions))
      {
        $conditions=null;
      }

      $head.='<!DOCTYPE html>';
      $head.=  implode("\n",$conditions);
      $head.='<html>';
      $head.='<head>';
      $head.='<body>';
      
      $tag_el='';
      $full_attr='';
     
     
      foreach ($tags as $tag => $attributes)
      {
      
      foreach ($attributes as $attr => $value)
      {
        $explode_tag=strstr($tag,'/');
        $closetag=(($explode_tag))?strstr($explode_tag,'/'):'';
        $closetag=(($closetag != ''))?' '.$closetag.'>':'</'.$tag.'>';
        $head.='  <'.$tag.' '.$attr.'="'.$value.'"'.$closetag;

      }
      
      }
      
      $head.=' </head>';
      
        if ($this->settings['edit_html'])
      {
         if (preg_match('/{head}/',$file_content,$match))
         {
             preg_replace('/'.$match[0].'/',$head,$file_content);
            
                return true;                

         }
      }

     

      $head=file_put_contents($file_path,$head);
      
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
    
    file_put_contents($this->tempdir.$js_folder.$file.'.'.'css',$content);

   }

   function createContentDiv($attributes,$inside=null,$content=null,$name='')
   {   
        $file =$this->style_GetTemp($this->html_file);
      
        $file_path=$this->style_GetTemp($this->html_file,true);
       $full_attr='';
       foreach ($attributes as $att => $value)
       {
         $full_attr.=' '.$attr.'="'.$value.'"'.' ';
       }
       $div='<div';
       $div.=$full_attr.'>';
       $div.=$content;
       $div.='</div>';
       $add=file_put_contents($file_path,$div,FILE_APPEND);

       return $add;
    if ($inside != null)
    {  
      if (!is_array($inside))
      {
         
        $inside=[];

      }
      $second_div='<div';
      $sec_full_attr='';
      $sec_content='';

      foreach ($inside as $cont => $atr)
      {
       foreach ($atr as $at => $val)
       {
         $sec_content.=$cont; 
         $sec_full_attr.=' '.$at.'="'.$val.'"'.' '.'>'; 
       }
        

        
      }
      $second_div.=$second_div.$sec_full_attr.$sec_content.'</div>';
      
      $add=file_put_contents($file_path,$second_div,FILE_APPEND);

      return $add;
     }
      if(preg_match('/{div name="([^"]*)" out="([^"]*)"}/',$file,$capt))
      {
        
          



      }
         
       
          
    }

     
   

   function createAside($attributes,$content=null,$number='')
   {
    
     $file_content=file_get_contents($this->html_file);
     
     $full_attr='';
     
     $endtag='</aside>';
     if (!is_array($attributes))
     {
        $attributes=[];
     }

     foreach ($attributes as $attr => $value)
     {
          $full_attr.=' '.$attr.'="'.$value.'"'.' ';

     }
    $tag='<aside'.$full_attr.'>';
    
    
     if ($this->settings['edit_html'])
      {   $regx='{aside';
          $regx.=$number;
          $regx.='\s';
          $regx.='in="([^"]*)"';
          $regx.='}';
         if (preg_match('#'.$regx.'#',$file_content,$match))
         {
             preg_replace('/'.$match[0].'/',$tag.'$1'.$endtag,$file_content);
            
                return true;                

         }
      }
   
    $tag.=$content;
   
    $tag.=$endtag;
   
    $aside=file_put_contents($this->html,$tag,FILE_APPEND);
      
      return $aside;  
   }
   
  

   function createFooter($attributes,$content=null)
   {
     $file_content=file_get_contents($this->html_file);
     
     $full_attr='';
     
     $endtag='</footer>';
     $endtag.='</body>';
     $endtag.='</html>';
     if (!is_array($attributes))
     {
        $attributes=[];
     }

     foreach ($attributes as $attr => $value)
     {
          $full_attr.=' '.$attr.'="'.$value.'"'.' ';

     }
    $tag='<footer'.$full_attr.'>';
    
    
     if ($this->settings['edit_html'])
      {
         if (preg_match('/{footer\sin="([^"]*)"}/',$file_content,$match))
         {
             preg_replace('/'.$match[0].'/',$tag.'$1'.$endtag,$file_content);
            
                return true;                

         }
      }
   
    $tag.=$content;
    $tag.=$endtag;
    $footer=file_put_contents($this->html,$tag,FILE_APPEND);
      
      return $footer;  
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

   function set_responsive()
   {
      $cssfile=$this->cssfile;
      
    
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


   function get_template_dir()
   {

     return SITE_URL.$this->tempdir;



   }

   function addError($errorname,$error)
   {

       
       return $this->errors[$errorname]=$error;
   }

   
   function getError($errorname=null,$getall=false)
   {
        
        if ($getall) {
            return $this->errors;
        }
        else{
          return $this->errors[$errorname];
        }
   }

   function print_error($spcific_error='')
   {
      
         if (!empty($spcific_error)) {
            $get_error=$this->errors[$spcific_error];

             die('<b>'.$spcific_error.':'.'</b>'.' '.$get_error);
         }
       else{
        $all_errors=implode("\n",$this->errors);
      
      die($all_errors);
   }
  }
}

class StyleEngineException extends EXCEPTION{ 

}
