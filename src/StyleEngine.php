<?php

/**
 *  StyleEngine (View Compiler)
 *  -------
 *  Realized by Nabil ( mohamedn085@gmail.com )
 *  Distributed under MIT License 
 *  @version 2.1
 */

namespace Style;

use Style\Interfaces\CustomRuleInterface;
use Style\CompileSectionsTrait;
use Style\CompileSpecialExperssionsTrait; 
use Style\Style;
     
use Style\Exceptions\NoAccessToDeleteCacheException;
use Style\Exceptions\NoAccessToWriteException;
use Style\Exceptions\ViewNotFoundException;
 
class StyleEngine implements CustomRuleInterface{

     use CompileSectionsTrait; 
     use CompileSpecialExperssionsTrait; 
      
        //-----------------------
        
           // Main configuration 
        
        //-----------------------




        /**
        *Template Base Path
        *
        *@var string
        */
           

            public  $tempdir;
        
        

        /**
        *Site Base Path
        *
        *@var string
        */
           

            const SITE_BASE_URL='SITE_URL'; //your site url 
        


        /**
 

        /**
        *Site Admin Base Path
        *
        *@var string
        */
           

            const SITE_ADMIN_URL='SITE_AD_URL'; //your Admin site url 
        


        /**
       
        /**
        *Site CSRF TOKEN
        *
        *@var string
        */
           

            const CSRF_TOKEN='DADQ!@#!@#EXZCZQWDQWE!@#!@#!P@#!@#P!P#@!'; //your  site csrf token 
        


        /**
       

         /**
       
        /**
        *Site remember token
        *
        *@var string
        */
           

            const REMEM_TOKEN='your  site remember token token'; //site remember
        


        /**


        *Template files Extention
        *
        *@var string
        */ 
            
            protected $tempex='php';

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



        public  $errors=[];



       /**
       * settings array contains info about temps , files, and orders in prosecss 
       * @var arra
       */

      
        public $settings=[];


      /**
      *cache path where to keep the compiled cache files 
      *@var 
      */


        protected $cache_dir='temp/';



      /**
      * array where StyleEngine store the variabls assined
      *@var arr
      */




        public  $vars=[];


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
      * all compiler expressions 
      * you can add new one using addTempRole Function in the view loader class
      *@var Static Array  
      */

       public static $exp = array(
                  'vars'=>['pattern'=>'{\$(\w+(?:\.\${0,1}[A-Za-z0-9_]+)*(?:(?:\[\${0,1}[A-Za-z0-9_]+\])|(?:\-\>\${0,1}[A-Za-z0-9_]+))*(.*?))}','func'=>'vars_Compile'],
                  'loop'        =>
                  ['pattern'=>'{%loop\s(\w+)%}','func'=>'loops_Compile'],
                  'endloop'     =>
                  ['pattern'=>'{%endloop%}','func'=>'loops_End_Compile'],
                  'inside_loop'=>
                  ['pattern'=>'{\{(\w+)\.?(\w+)?\}}','func'=> 'inside_loop_compile'],
                  'include_file'=>
                  ['pattern'=>'@display\((.*?)\)','func'=>'compile_Get_File'],
                  'function'    =>
                  ['pattern'=>'{%func\s(echo+|)\s?(\w+)\(([^%]*)\)%}','func'=>'compileFunctions'],
                  'if'          =>
                  ['pattern'=>'{%if\s(.*?)%}','func'=>'compile_if_cond'],
                  'endif'       =>
                  ['pattern'=>'{%endif%}','func'=> 'compile_endif_cond'],
                  'php_start'   =>
                  ['pattern'=>'\[php\]','func'=>'compile_php_start'],
                  'php_end'     =>
                  ['pattern'=>'\[\/php\]','func'=>'compile_php_end'],
                  'cons'        =>
                  ['pattern'=>'{\#([^}]*)}','func'=>'compile_cons'],
                  'var_declare' =>
                  ['pattern'=>'{%(\w+)\=(.*?)}','func'=>'var_declarer'],
                  'elseif'      =>
                  ['pattern'=>'{%elseif\s(.*?)%}','func'=> 'else_if_Compile'],
                  'else'        =>
                  ['pattern'=>'{%else%}','func'=>'else__Compile'],
                  'comment'     =>
                  ['pattern'=>'\[comment\]([^\f]*?)\[\/comment\]','func'=>'comment_tag_Compile'],
                  'main_url'    =>
                  ['pattern'=>'\{url\((.*?)\)\}','func'=>'url_change'],
                  'url_admin'   =>
                  ['pattern'=>'\{url_admin\((.*?)\)\}','func'=>'url_admin'],
                  'csrf'        =>
                  ['pattern'=>'\{csrf_input\}','func'=>'csrf'],
                  'remem_token' =>
                  ['pattern'=>'\{remember_input\}','func'=>'remem_token'],
                  'input_method'=>
                  ['pattern'=>'\{input_method\(([A-Z]+)\)\}','func'=>'input_method'],
                  'foreach'     =>
                  ['pattern'=>'@foreach\((.*?)\)','func'=>'compile_foreach'],
                  'endforeach'  =>
                  ['pattern'=>'@endforeach','func'=>'end_for_each_compile'],
                  'printing'    =>  ['pattern'=>'{%(.*?)%}','func'=>'printing_compile'],
                  'endfalse'    =>['pattern'=>'@backwithfalse','func'=>'end_with_false']
                 );


       public static $footers=[]; // array of injected view footer content

       protected static $last_view; // get last view showed name

       public static $last_cache_file=''; //last view cachedfile path;

       protected static $loops=[]; // storing loop names
       


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
    protected function show($template_name,$data=[])
    {
         // explode filename path 
           //$tpl_name='';
         if(strpos($template_name,'.') != false)
         {  
            $tpl_name=str_replace('.','/',DIRECTORY_SEPARATOR.$template_name);
             
             $this->tempdir.=rtrim($tpl_name,basename($tpl_name));      
         }
         else{
          $tpl_name=$template_name;
         }
         // checks if it has to compile the template or not 
        
         //echo $this->tempdir;
        //  dd(basename($tpl_name));
         $this->check_template(basename($tpl_name));
           
         $this->assign($data);

         self::$last_cache_file = $this->settings['compiled_file_name'];

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
        throw new NoAccessToDeleteCacheException('you dont have access from the server to do this delete operation.check server support or set check_cache_file=false in StyleEngine file');
      }

        
      }
       
       return $this->settings['compiled_file_name']=$compiled_file_name; 
    
     }
     
     //checks if has to compile template or not 
     // return true if template changed
     //return null when no changes did not template 

     protected function check_template($tpl_name)
     {   
 
        $tpl_path=$this->style_GetTemp($tpl_name,true);
       
       
        $compiled_file_name=$this->cache_dir.DIRECTORY_SEPARATOR.$tpl_name.'.'.md5($tpl_path).'.stl'.'.php';
         // dd($tpl_path);

        $this->cache($compiled_file_name); //check cache file is expired or not 
        
        // file doesn't exsist, or the template was updated, styleengine will compile the template
      
       if(!file_exists($tpl_path) )
       {

          throw new ViewNotFoundException('Template'.' '.'<b>'.$tpl_name.'</b>'.' Not Found Please Check template <b>template path not resloved:<b> '.$tpl_path);
          

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

    private function compile_template($template_full_path)
    {
        // get the template path

      $template=$template_full_path;
      
      // array with full regx
      
      $exps= self::$exp;
       
       
       // disable php tags 
      $temp=(($this->enable_php === false))?str_replace(['<?','?>'],['&lt;?','?&gt;'],$template):$template;

      //inject content into the footer of page if page has a parent view
      //   dd($exps['extendview']);


    //  dd($code_replace);
      /*

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
      
      $code_replace=preg_replace_callback('#'.$exps['main_url'].'#',array($this,'url_change'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['url_admin'].'#',array($this,'url_admin'),$code_replace);
 
      //$code_replace=preg_replace_callback('#'.$exps['input_method'].'#',array($this,'input_method'),$code_replace);

      //$code_replace=preg_replace_callback('#'.$exps['remem_token'].'#',array($this,'remem_token'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['csrf'].'#',array($this,'csrf'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['section_start'].'#',array($this,'section_start'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['section_end'].'#',array($this,'end_section'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['show_section'].'#',array($this,'section_show'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['section_new'].'#',array($this,'section_compile'),$code_replace);

      $code_replace=preg_replace_callback('#'.$exps['include'].'#',array($this,'compile_include'),$code_replace);
       $code_replace=preg_replace_callback('#'.$exps['foreach'].'#',array($this,'compile_foreach'),$code_replace);
       $code_replace=preg_replace_callback('#'.$exps['endforeach'].'#',array($this,'end_for_each_compile'),$code_replace);   
       $code_replace=preg_replace_callback('#'.$exps['printing'].'#',array($this,'printing_compile'),$code_replace);
        $code_replace=preg_replace_callback('#'.$exps['endfalse'].'#',array($this,'end_with_false'),$code_replace);
     */
   
   //preg_replace_callback_array works on php 7.0 or above versions
    $exp_array = [];

    foreach($exps as $key=>$value)
    { 
        if( $exps[$key]['func'] instanceof Closure || is_callable($exps[$key]['func']))
        {
           $exp_array['#'.$exps[$key]['pattern'].'#'] =$exps[$key]['func'];
        }
        else
        {
            $exp_array['#'.$exps[$key]['pattern'].'#'] = [ get_class($this), $exps[$key]['func'] ];
        }      
    
    }
 
    
    $code_replace=preg_replace_callback_array($exp_array,$temp);
    
    $code_replace = trim($code_replace);
         
      $curr_v=self::$last_view;

      if(count(self::$footers))
      {
         $code_replace =$this->addFooters($code_replace);

           //remove the footer of current view from footers array
            unset(self::$footers[$curr_v]);
            //remove extend view compiling to avoid duplication of compiling the pattern
            unset($exps['extendview']);
            //dd($exps);
           // dd($code_replace);
      }
   
        return $code_replace;
    }

    public function addTempRole($regx_name,$regx,$func)
    {   
        $regx_name = $regx_name;

        self::$exp[$regx_name]=['pattern'=>$regx,'func'=>$func];

        return $regx;
    }


    // compile variables inside conds and func params
    
    function compile_vars_inside($code)
    {
      
      return preg_replace('/{\@([^}|^{]*)}/','$'.'$1',$code); 

    }
    // compile constans inside conds and func params
    
    function compile_cons_inside($code)
    {
      return preg_replace('/\((\w+)\)/','$1',$code);  

    }
     
     // compile constants

    function compile_cons($capt)
    {

      return '<?php echo _esc('.' '.$capt[1].'); ?>';

    }
    
    function var_declarer($capt)
    {  
   
      $value=(new self)->compile_vars_inside($capt[2]);
      
      $value=(new self)->compile_cons_inside($value);
 

      return '<?php $'.$capt[1].'='.$value.'; ?>';

    }
     
     //Compiling multidimensional array 
    function inside_loop_compile($capt)
    {
     $full_loop_word='';
      
      if ($capt[1] =='value' && count($capt)==3)
      {
        $full_loop_word .='<?php echo'.' '.'_esc($value'.'["'.$capt[2].'"]'.');?>';
      }

      elseif ($capt[1]=='key')
      {
        $value=((isset($capt[2])) && $capt[2]=='value')?'.$'.$capt[2].'':null;

        $full_loop_word .='<?php echo'.' '.'_esc($'.$capt[1].''.$value.');?>';            
      }
           
          return $full_loop_word;
    }
    


    function compileFunctions($capt)
    {  
      $phpright='<?php';
      
      $phpleft=';?>';

      $params=(new self)->compile_vars_inside($capt[3]);
      
      $params=(new self)->compile_cons_inside($params);
        
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
       return '<?php echo'.' '.'_esc($'.$capt[1].');?>';

    }
    
    function style_GetTemp($tempname,$getpath=false)
    {
       if ($getpath)
      {
       return $this->tempdir.DIRECTORY_SEPARATOR.$tempname.'.stl.'.$this->tempex;
      }
       return file_get_contents($this->tempdir.DIRECTORY_SEPARATOR.$tempname.'.stl.'.$this->tempex);
      
      
    }
 
    public function compile_Get_File($capt)
    {
       $tpl   = Style::$dir;
       $chdir = Style::$chdir;

     return'<?php $display=new '.Style::class.'(\''.$tpl.'\',\''.$chdir.'\');$display->render('.$capt[1].');?>';

    }

    function compile_include($capt)
    {
       return "<?php include('$capt[1]');?>";
    }
    
    function compile_php_start($capt)
    {
      return '<?php'.' ';

    }

    function compile_php_end($capt)
    {
      return '?>';

    }

    function compile_if_cond($capt)
    { 
      $condition=(new self)->compile_vars_inside($capt[1]);

      $condition=(new self)->compile_cons_inside($condition);
        
     return $compiled_code="<?php if($condition):?>";
    }

    function compile_endif_cond($capt)
    {
         return "<?php   endif;   ?>";
    }

    function comment_tag_Compile($capt)
    {    
        $capt[0]=null;

        return $capt[0];

    }

    function else_if_Compile($capt)
    {
       $condition=(new self)->compile_vars_inside($capt[1]);

       $condition=(new self)->compile_cons_inside($condition);
        
        return "<?php elseif($condition): ?>";

    }

    function compile_foreach($capt)
    {
      
       return "<?php foreach(".$capt[1]."): ?>";

    }

    function printing_compile($capt)
    {   
       if (strstr($capt[1],'('))
       {
          $func=explode('(',$capt[1]);
          $func_name=rtrim($func[0],')');
           if (function_exists($func_name))
           {
                 return '<?php '.$capt[1].'; ?>';
           }
       }
        if (is_callable($capt[1]) || $capt[1] instanceof Closure)
       {
          return '<?php '.$capt[1].'()'.'; ?>';
       }  

       if (is_object($capt[1]))
       {
          return '<?php '.$capt[1].'; ?>';
       }

       return '<?php echo'.' '.$capt[1].'; ?>';
    }

    function end_for_each_compile($capt)
    {
      
       return "<?php endforeach ?>";

    }

    function else__Compile($capt)
    {    
        //echo $capt[1];    
        return '<?php else: ?>'."\r\n";
    }
    function end_with_false($capt)
    {
      return '<?php return false; ?>';
    }

    function url_change($capt)
    {
       return self::SITE_BASE_URL.$capt[1];
    }

    function url_admin($capt)
    {
      return self::SITE_ADMIN_URL.$capt[1];
    }
    
    function csrf($capt){

    return '<input type="hidden" name="_tcsrf" value="'.CSRF_TOKEN.'">';
    }

    function remem_token($capt)
    {
      return '<input type="hidden" name="_rtoken" value="'.REMEM_TOKEN.'"';
    }

    function input_method($capt)
    {
         
        return '<input type="hidden" name="'.$capt[1].'"';  
        
      
    }


    function change_template_folder($folder_name,$createfolder=false)
    {  
      $this->tempdir.=$folder_name;

      if ($createfolder && !is_dir($this->tempdir))
      {
         
         mkdir($this->tempdir,0777,true);

         
      if(!is_writable($this->tempdir))
      {

       throw new NoAccessToWriteException('you dont have access to create new folder on the server please check a write permissinos');

      }

      }
     
     }
    
   

   

    protected function addFooters($result)
    {   

        return ltrim($result, "\n")
                .implode("\n", array_reverse(self::$footers));
    }

    protected function setFooter($data)
    {
        self::$footers=$data;
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
