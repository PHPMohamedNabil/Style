<?php

/**
 *  View Loader for StyleEngine Compiler
 *  -------
 *  Realized by Nabil ( mohamedn085@gmail.com )
 *  Distributed under MIT License 
 *  @version 2.1
 */

namespace Style;

use Style\StyleEngine;
use Style\HtmlBuliderTrait;
use Exception;
use Style\Exceptions\NoAccessToDeleteCacheException;
use Style\Exceptions\NoAccessToWriteException;
use Style\Exceptions\ViewNotFoundException;


class Style extends StyleEngine{

   // use CompileSectionsTrait;
   
    use HtmlBuliderTrait;
    
    //-- modifers --//

    
      //*See StyleEngine file for all modifers you can overwrite it here


	   public static $dir='template/';


	   public static $chdir =  'template/temp/';



    //-- end modifers --//


    /** 
        *@__construct
        * Add new view expressions
        * you can add as many as you want new expressions for view
        * first implementing traits here that has a functions to be a callback along with every expressions, and then register it her in costructor using addTempRole function 
        * to use addTempRole CustomRuleInterface must be implemented in any parent view classes
    * */

	 public function __construct($temp_path,$cache_path)
	 {   
        
        //setting template and cache folder pathes

        $this->tempdir   = $temp_path;
       	$this->cache_dir = $cache_path;

	 	//sections statments

       	$this->addTempRole('extendview','@spread\(\'(.*?)\'\)','extendView');
	 	$this->addTempRole('add_section','@addsection\(\'(.*?)\'\)','implementSection');
	 	$this->addTempRole('section','\@section\(\'(.*?)\'\)','compileStartSection');
	 	$this->addTempRole('endsection','\@endsection','compileEndSection');
	 	
	 	//switch statment
	  
	 	$this->addTempRole('switch','@switch\((.*?\)?) *?\)','CompileSwitch');
	 	$this->addTempRole('break','@break','CompileBreakWord');
	 	$this->addTempRole('case','@case\((.*?\)?) *?\)','CompileCaseWord');	
	 	$this->addTempRole('default','@default','compileDefualtWord');
	 	$this->addTempRole('endswitch','\@endswitch','CompileEndSwitch');

	 	// for loop statment
       
	 	$this->addTempRole('for','@for\((.*?)\)','forCompile');
	 	$this->addTempRole('endfor','\@endfor','endFor');

	 	// continue word and while loop

	 	$this->addTempRole('while','@while\((.*?\)?) *?\)','CompileWhile');
	 	$this->addTempRole('continue','@continue','compileContinue');
	 	$this->addTempRole('endwhile','@endwhile','CompileEndWhile');
	 	
	 	// hard compile (unique feature) for compiling template from another one by sending data to template regarding to html tag position.

	 	 $this->addTempRole('hardcompile','\@hardcompile\(((\w+\.?.*?)\[(.*?)\] (?:before|after|within) \w+\:\w+ data\:\"(.*?)\")\)','hardCompile');


	 	 
	 	 	 $this->tempdir   = $temp_path;
             $this->cache_dir = $cache_path;
             self::$dir   = $temp_path;
             self::$chdir = $cache_path;

	 	 

          

           $this->tempdir=self::$dir;
           $this->cache_dir =self::$chdir;

          
	 }

  /**
   * load view
   * eg. loading view of the specific template page
   * @param string  $view   name of the view to be loaded
   * @param array   $data   data to be assigned to the view
   * @param boolean $string (if = true : Render view and return it as string) 
   */

	public function render($view,$data=[],$string=false)
	{     
      self::$last_view = $view;
		//return dd(self::$exp);
	  try
	  { 
	     if($string)
		  {
			 ob_start();
                $this->show($view,$data);
             $st = ob_get_clean();
               
             
             return $st;
		  }
		     $this->show($view,$data);

	 }
	 catch(NoAccessToDeleteCacheException $e)
	 {
	 	echo $e->getMessage();
	 }
	 catch(ViewNotFoundException $e)
	 {
	 	echo $e->getMessage();
	 }
     catch(NoAccessToWriteException $e)
	 {
	 	echo $e->getMessage();
	 }
		
	}
    


	
    

}


