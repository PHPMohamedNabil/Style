<?php

namespace Style;

use Exception;
use OutOfRangeException;
use Style\Style;

trait CompileSectionsTrait{
    
    
    protected static $sections=[];

    protected static $yield=[];

  
	public function extendView($regx)
	{  
		$footer= '<?php $view= new '.get_class().';$view->compileFull(\''.$regx[1].'\');?>';
         
         self::$footers[self::$last_view]=$footer;
         
		 return '';
	}
	

	public function compileStartSection($regx)
	{
		return '<?php $view= new '.get_class().';$view->sectionStart(\''.$regx[1].'\');?>';
	}

	public  function sectionStart($name) 
	{    
       
            ob_start();
       
	        self::$sections['yield__'.$name]='';

	        self::$yield[] = 'yield__'.$name;

	}

	public function compileEndSection()
	{ 
			$string ='<?php $view= new '.get_class().';$view->sectionEnd();
		           ?>';
		           
		return $string;
	}

	public function sectionEnd()
	{  
		if (empty(self::$yield) || count(self::$yield)<1)
		{  
			 ob_end_clean();
          throw new OutOfRangeException('Section ended without started');
      }

        $last = array_pop(self::$yield);
         self::$sections[$last] = ob_get_contents();
		 ob_end_clean();
		 ob_clean();
	  //dd(self::$yield);
	
	}

	public function compileFull($parent_view,$clear_footers=false,$string=false,$data=[])
	{    

		 $viewer = new Style(Style::$dir,Style::$chdir);

		if($clear_footers)
		{   
			 
			  $viewer::$footers=[];

			  	if($string)
		      {   
             return $viewer->render($parent_view,$data,true); 
		      }

        return $viewer->render($parent_view,$data); 
       
		}

		if($string)
		{   
        return $viewer->render($parent_view,$data,true); 
		}

		return $viewer->render($parent_view,$data);
		
	}
    

    public function implementSection($regx)
	{    
		 

		return '<?php $new_view= new '.get_class().';echo $new_view->yieldsection(\''.$regx[1].'\');
		           ?>';;

	}

	public function yieldsection($section_name)
	{  
		$content = '';
		 if(isset(self::$sections['yield__'.$section_name]))
		 {
		 	$content = self::$sections['yield__'.$section_name];
		 }
		 return $content;
	}


}