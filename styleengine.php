<?php
/**
 *  styleengine
 *  -------
 *  Realized by Nabil 
 *  @version 1.0.0
 */
class StyleEngine{
 
        public  $tempdir='views/templates/';
        private $tempex='html';
        private $check_template_update = true;
        private $cache_exptime=3600;
        private $errors=[];
        private $settings=[];
        public  $cache_dir='views/temp/';
        public  $vars=[];
        private $html_file='';
        private $enable_php=false;

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
     
     function check_template($tpl_name)
     {   
      
        $tpl_path=$this->style_GetTemp($tpl_name,true);
        $compiled_file_name=$this->cache_dir.$tpl_name.md5($tpl_path).'.st'.'.php';
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
         
            $compiled_file=file_put_contents($compiled_file_name,$compile);
            
             
        $this->settings['compiled_file_name']=$compiled_file_name;
    }

    protected function compile_template($template_full_path)
    {

      $template=$template_full_path;
      $exps=array('vars'        =>'{\$(\w+)}',
                  'loop'        =>'{%loop\s(\w+)%}',
                  'endloop'     =>'{%endloop%}',
                  'inside_loop'=>'{\$(\w+)\.?(\w+)?}',
                  'include_file'=>'{%display\="(.*)"%}',
                  'function'    =>'{%func\s(echo+|)\s?(.*)\((.*)\)%}',
                  'if'          =>'{%if\s(.*)%}',
                  'endif'       =>'{%endif%}',
                  'php_start'   =>'\[php\]',
                  'php_end'     =>'\[\/php\]'
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
      $code_replace=preg_replace_callback('#'.$exps['php_start'].'#',array($this,'compile_php_start'),$code_replace);
      $code_replace=preg_replace_callback('#'.$exps['php_end'].'#',array($this,'compile_php_end'),$code_replace);
       
     
       
      
      
         
         return $code_replace;


    }

  

    function compile_vars_inside($code)
    {
      
    
        
      return preg_replace('/{\@(\w+)}/','$'.$this->vars_filter('$1'),$code);
       
         
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
      $echoword='echo';
      
      $params=$this->compile_vars_inside($capt[3]);
      return $compiledcode=$phpright.' '.$capt[1].' '.$capt[2].'('.$params.')'.' '.$phpleft;
    }
    
    function loops_Compile($capt)
    {   
        return $compilecode='<?php if( isset($'.$capt[1].') && is_array($'.$capt[1].') && sizeof($'.$capt[1].') ) foreach($'.$capt[1].' as $key=>$value){?>';
    }

    function loops_End_Compile($capt)
    {
        return $compilecode='<?php }?>';
    }

    function vars_Compile($capt)
    { 
      
      $var=$this->vars_filter($capt[1]);
      
      return $compilecode='<?php echo'.' '.'$'.$var.';?>';
    }
    
    function vars_filter($var)
    {
      return htmlspecialchars(htmlentities(addslashes((trim($var)))));
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
      $include_file='<?php $stl = new '.get_class($this).';'.' '.'$stl->show( basename("'.$file.'"));?>';
       return $include_file;



    }
    
    function compile_php_start($capt)
    {
      return $compiledcode='<?php';
    }
    function compile_php_end($capt)
    {
      return $compiled_code='?>';
    }
    function compile_if_cond($capt)
    {

     $condition=$this->compile_vars_inside($capt[1]);
     
     
     return $compiled_code="<?php if($condition){?>";


    }

    function compile_endif_cond($capt)
    {

     return $compiled_code="<?php   }     ?>";


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

      else{
        
        $temp_path=$this->tempdir.$temp_path.'/';
      }
        if (!is_dir($temp_path))
     {
     
      mkdir($temp_path,0777,true);
     
     }
    
      if (!is_writable($temp_path))
      {
       $this->addError('access_to_write','you dont have access to create new folder on the server please check a write permissinos');
       $this->print_error('access_to_write');
      }
      
      file_put_contents($temp_path.$this->html_file.'.'.$this->tempex,'');

    
     
   } 
   
   function createHeadElements($tag,$attr,$closetag=null,$conditions=null)
   {
      $file_path         =$this->style_GetTemp($this->html_file,true);
      $file_content      =$this->style_GetTemp($this->html_file);
      $head              ='';
      $replce            ='';
      $append            ='';
      $head.='<!DOCTYPE html>'.
      $conditions.
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

      
      
      if (!empty($file_content))
      {
        $append.='<'.$tag.' ';
        foreach ($attr as $attributes => $value)
      {
       
        $append.=$attributes.'="'.$value.'"'.' ';
       
           if (preg_match('#\<head\>(.*)\<\/head\>#',$file_content))
           {
             

           }
          
          
      
      }  
      $closetag=(($closetag != null))?$closetag:'</'.$tag.'>';
      $append.=$closetag;
     $replce.=preg_replace('#\<head\>(.*)\<\/head\>#','<head>'.'$1'.$append.'</head>',$file_content); 
      }
      
      
      if (!file_exists($file_path))
      {
         $this->addError('Wrong_FILE_NAME','Wrong HTMLFILENAME please check html file name or maybe create a new html file  from createHtml method ');
         $this->print_error('Wrong_FILE_NAME');
         clearstatcache();
      }
     
      if ($replce == '') {
          file_put_contents($file_path,$head);
          unset($replce);
      }
      else{
        unset($head);
         return true;
      
      }

      

   }
   
   
   function createStyleFile()
   {



   }

   function createScriptFile()
   {



   }

   function createContentElements()
   {




   }

   function createContentAside()
   {



   }
   
   function CreateHeader()
   {


   }

   function createContentFooter()
   {



   }

   function removeHtml()
   {



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

