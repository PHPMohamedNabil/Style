<?php
/**
 *  styleengine
 *  -------
 *  Realized by Nabil 
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
        







        private $errors=[];
        public $settings=[];
        private $cache_dir='views/temp/';
        public  $vars=[];
        private $html_file='';
        private $cssfile='';
        private $jsfile='';
        public  $enable_php=false;
        public  $enable_cache=true;
       
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
     
    public function show($template_name)
    {
           
         $this->check_template($template_name);
         
         
            extract($this->vars);
         
         include ($this->settings['compiled_file_name']);
    
    }
     
     public function cache()
     {
      
    
     
     }

     protected function check_template($tpl_name)
     {   
 
        $tpl_path=$this->style_GetTemp($tpl_name,true);

        $compiled_file_name=$this->cache_dir.$tpl_name.'.'.md5($tpl_path).'.st'.'.php';

        $this->settings['compiled_file_name']=$compiled_file_name;
      // file doesn't exsist, or the template was updated, styleengine will compile the template

       if(!file_exists($tpl_path) )
       {
          $this->addError('FileNotFound','Templaete'.' '.'<b>'.$tpl_name.'</b>'.' Not Found Please Check template name');
          $this->print_error('FileNotFound');
          
       }

      if( !file_exists( $compiled_file_name ) || filemtime($compiled_file_name) < filemtime( $tpl_path )  )
      { 
        $this->compile_File($tpl_name,$compiled_file_name);
        return true;
      }
       
       return null;
    }
    
    protected function compile_File($template_full_path,$compiled_file_name)
    {
        $temp=$this->style_GetTemp($template_full_path);
           
           if(!is_dir( $this->cache_dir ))
           {
             mkdir($this->cache_dir, 0755, true );
           }
            
            $compile=$this->compile_template($temp);
            
            $compile = str_replace( "?>\n", "?>\n\n",$compile);
         
            $compiled_file=file_put_contents($compiled_file_name,$compile);
            
             
        $this->settings['compiled_file_name']=$compiled_file_name;
            
             
    }

    protected function compile_template($template_full_path)
    {
        
      $template=$template_full_path;
      
      $exps=array('vars'        =>'{\$(\w+[^}*])}',
                  'loop'        =>'{%loop\s(\w+)%}',
                  'endloop'     =>'{%endloop%}',
                  'inside_loop'=>'{\$(\w+)\.?(\w+)?}',
                  'include_file'=>'{%display\=\"(.*)\"%}',
                  'function'    =>'{%func\s(echo+|)\s?(\w+)\(([^%]*)\)%}',
                  'if'          =>'{%if\s([^%]*)\%\}',
                  'endif'       =>'{%endif%}',
                  'php_start'   =>'\[php\]',
                  'php_end'     =>'\[\/php\]',
                  'cons'        =>'{\#(.*)}',
                  'var_declare' =>'{\$(\w+)\=(.*)}',
                  'elseif'      =>'{%elseif\s([^%]*)\%\}',
                  'else'        =>'{%else="([^\f]*)"%}',
                  'comment'     =>'\[comment\]([^\f]*)\[\/comment\]',
                 );

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
    
    function compile_vars_inside($code)
    {
      
      return preg_replace('/{\@(\w+)}/','$'.'$1',$code); 

    }

    function compile_cons_inside($code)
    {
      return preg_replace('/{\#(\w+)\#}/','$1',$code);  

    }

    function compile_cons($capt)
    {

      return '<?php echo'.' '.$capt[1].' ?>';

    }
    
    function var_declarer($capt)
    {

      return '<?php $'.$capt[1].'="'.$capt[2].'"; ?>';

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

    public static function vars_Compile($capt)
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

    function change_template_folder($them_name_folder,$chekfolder=false)
    {  
      $this->tempdir.=$them_name_folder;

      if ($chekfolder && !is_dir($this->tempdir))
      {
         
        mkdir($this->tempdir,0777,true);
      if(!is_writable($this->tempdir))
      {

        $this->addError('access_to_write','you dont have access to create new folder on the server please check a write permissinos');

         $this->print_error('access_to_write');
      }

      }
     
     }
    
   function createHtml($html_file,$temp_path=null)
   {
       
       $this->html_file=$html_file;

      if ($temp_path === null)
      {
           $temp_path=$this->tempdir;
      }

      else
      {
        
        $temp_path=$this->tempdir.$temp_path.'/';
      }
     
     if (!is_dir($temp_path))
     {
     
      mkdir($temp_path,0777,true);
     
     }
    
      if (!is_writable($temp_path))
      {
      throw new StyleEngine_Exception ('you dont have access to create new folder on the server please check a write permissinos');
      
      }
      
       file_put_contents($temp_path.$this->html_file.'.'.$this->tempex,'');
    
    } 

   function editeHtml($file)
   {
       $this->html_file=$file;
       $this->settings['edit_html']=true;
       
        return $this->html_file;

   }
   
   function createHeadElements($tag,$attr,$closetag=null,$conditions=null)
   {
      
      $file_path         =$this->style_GetTemp($this->html_file,true);

      $file_content      =$this->style_GetTemp($this->html_file);

      $head              ='';

      $replce            ='';

      $append            ='';

      $head.='<!DOCTYPE html>'.
      implode('',$conditions).
         '<html>
           <head>';

      $full_attr='';
     
     
      foreach ($attr as $attributes => $value)
      {
        $full_attr.=' '.$attributes.'="'.$value.'"';

      }
      $closetag=(($closetag != null))?$closetag.'>':'</'.$tag.'>';

      $head.='<'.$tag.$full_attr.$closetag;

      $head.='</head>';

      
      
      if ($this->settings['edit_html'])
      {
        $append.='<'.$tag.' ';

      foreach ($attr as $attributes => $value)
      {
       
        $append.=$attributes.'="'.$value.'"'.' ';
          
      }  
      $closetag=(($closetag != null))?$closetag:'</'.$tag.'>';

      $append.=$closetag;
         var_dump($append);
       $edit=preg_replace(
          '/<head\>(.*)\<\/head\>/',
          '',
          $file_content);
       
        $replce.=$edit;

      
     
      }
      
      
      if (!file_exists($file_path))
      {
         $this->addError('Wrong_FILE_NAME','Wrong HTML_FILE_NAME please check html file name or create a new html file  from createHtml method ');
         $this->print_error('Wrong_FILE_NAME');
      }
     
      if ($replce == '') {
         
     file_put_contents($file_path,$content,FILE_APPEND);
          
      }
   
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

   function createContentDiv($attr,$inside=null,$content)
   {
    $file =$this->style_GetTemp($this->html_file);
    $file_path=$this->style_GetTemp($this->html_file,true);
    $div='';
    $atr='';
    $replace='';
    $inside='';
    $div.='<div';

    foreach ($attr as $attributes => $value)
    {
      $atr.=' '.$attributes.'="'.$value.'"'.' ';
    }
    $div.=$atr.'>';
    $div.=$content;
    $div.='</div>';
    
    if ($inside != null)
    {  
      foreach ($inside as $key => $value)
      {
         $inside.=$key.'="'.$value.'"'.' ';
      }
      
      if(preg_match('#\<div\s+('.$inside.')(.*)\>(.*)<\/div\>#',$file,$capt))
      {
        $replace.=preg_replace('#\<div\s+('.$capt[1].')(.*)\>(.*)<\/div\>#',
        '<div'.' '.$capt[1].' '.$capt[2].'>'.$capt[3].$div.'</div></div>',$file);
      }
         
       file_put_contents($file_path,$replace);
          
    }

     
   }

   function createAside()
   {



   
   }
   
  

   function createFooter()
   {



   }

   function set_cssfile_path($cssfile,$folder=null)
   {
     $folder=(($folder != null))?$folder.'/':null;

    $this->cssfile=$this->tempdir.$folder.$cssfile;

   }
   
   function set_jsfile_path($jsfile,$folder=null)
   {
     $folder=(($folder != null))?$folder.'/':null;

     $this->jsfile=$this->tempdir.$jsfile;

   }

   function make_responsive()
   {
     $stylefile=$this->cssfile;
     
    

   }
   function edit_responsive_for($style_file_path,$device,$elements)
   {

      

     return ['info'=>'you can modify this from your style file '
     ,'media_queris'=>[
       ''
     ]
     ];

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
   
   function removeStyleFolder()
   {
  
   

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
        $all_errors=implode('\n',$this->errors);
      
      die($all_errors);
   }
  }
}



 //style_SetVars(['mohamed','nabil']);
 
 //print_r();

// $var=style_assign('name','ahmed');
 //style_assign('address','36 ahmed zaki');
 //style_assign('ya5awal','s');
// style_assign(['name','value']);

 //$news=array('sadadada'=>1223);
 //style_assign('news',$news);

//$temp=compile_template(style_GetTemp('header'));
 //echo analyze_code($temp);
   //compilevarsLoop('{$}');
 // $array1=['name'=>'mohamed','addres'=>'nabil'];
  //$array=['name','addres'];
/// var_dump(array_search($array[1],array_keys($array1)));
 //compile_file('header');
   
  

   //show('header');
   //echo '<br>';

   //style_assign('data',['id'=>1,'username'=>'nabil','password'=>md5(mt_rand(1,1000))]);
  // show('reporter');
 //$style=new StyleEngine;

 //$style->addThemFolder('feature2',true);

