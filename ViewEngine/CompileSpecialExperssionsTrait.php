<?php

namespace App\Core\ViewEngine;

use App\Core\View;


trait CompileSpecialExperssionsTrait{
     
     
     public static $first_case='';

     public function CompileSwitch($capt)
     {   
     	   self::$first_case=true;
            
           return '<?php  $stl = new '.get_class().' ?>
           <?php $stl::$first_case=true;?>
           <?php switch('.$capt[1].'): ';
     }

     public function CompileCaseWord($capt)
     {
     	if(self::$first_case)
     	{    
     		self::$first_case= false;
     		return 'case '.$capt[1].':'.'?>';
     	}

     	    return '<?php case '.$capt[1].':'.'?>';
     	 

     }

     public function CompileBreakWord($capt)
     {
          return '<?php break;?>';
     }


     public function compileDefualtWord($capt)
     {
     	 return '<?php default:?>';

     }


     public function CompileEndSwitch($capt)
     {
     	 return '<?php endswitch?>';

     }

     public function forCompile($capt)
     {
     	 return '<?php for('.$capt[1].'): ?>';

     }

      public function endFor($capt)
     {
     	 return '<?php endfor; ?>';

     }


     public function hardCompile($capt)
     {
     	$arguments     = explode(' ',$capt[1]);

     	$view_name     = $arguments[0];

     	$condition     = (in_array($arguments[1],['before','after','within']))?$arguments[1]:'before';

     	$tag           = explode(':',$arguments[2]);

     	$tag_name      = $tag[0];

     	$class         = $tag[1];

     	$full_tag_name =[$tag_name,$class];

     	$data          = $capt[2];
        
         //$full_tag_slashesh_issue=str_replace('"','\"',$full_tag_name);
        
       // dd($full_tag_slashesh_issue);
          
        //dd((new self)->deleteCasheBeforeHardCompile($view_name));

          $string = '<?php	
          $string_view          = new '.get_class(new view).';
        
                  $view_string   = $string_view::compileFull("'.$view_name.'",true,true);

             $stl = new '.get_class().';
          
        ob_clean();
          ob_start();
              '.$data.';
     	 $new_data = ob_get_clean();
         ob_clean();
            $full_tag_name=[\''.$tag_name.'\',\''.$class.'\'];
     	   $new_replaced  = $stl->switchData("'.$condition.'",$full_tag_name,"'.$tag_name.'",$new_data,$view_string);
     	   $new_replaced=stripcslashes($new_replaced);
           $string_view->deleteCasheBeforeHardCompile($stl::$last_cache_file);
file_put_contents($string_view->tempdir.$stl->style_GetTemp("'.$view_name.'",true),$new_replaced);
     	  ?>
<!-- there was hard compule done her to injected in view '.$view_name.'.stl.php  -->
     	  ';
        
         return   $string;
        

     }

    public function afterhardCompile($data,$temp,$expeission)
    { 
        $var = "#(<$expeission[0] (.*?)class=\"$expeission[1]\s?(.*?)\"(.*?)([\w\W]*?)\<\/$expeission[0]\>).*\n?[\w\W]*?\n?.*#";
        
         if(!preg_match($var,$temp,$res))
            return $temp;

           preg_match($var,$temp,$res);
      //  dd($res[2]."\r\n".$data);
         $prepare_data = str_ireplace($res[0],'place_holder',$temp);

          //dd($prepare_data);
      
           $final_data = preg_replace('#place_holder#',$res[1]."\r\n".$data,$prepare_data);
          //
          //  dd($final_data);

          return $final_data;
    }

     public function switchData($case,$full_tag_name,$tag=null,$data,$temp)
     {
     	//dd($full_tag_name);

     	 switch ($case) {
     	 	case 'within':
     	 	 return (new self)->withinHardCompile($data,$temp,$full_tag_name);
     	 		break;
     	    case 'before':
     	 	 return (new self)->beforeHardCompile($data,$temp,$full_tag_name);
     	 	 case 'after':
     	 	 return (new self)->afterhardCompile($data,$temp,$full_tag_name);
     	 	
     	 	default:
     	 		return $full_tag_name;
     	 }

    }

    public function beforeHardCompile($data,$temp,$expeission)
    {   
        $var = "#(.*?)\n?(<$expeission[0] (.*?)class=\"$expeission[1]\s?(.*?)\"(.*?)([\w\W]*?)\<\/$expeission[0]\>.*\n?[\w\W]*?)#";
           if(!preg_match($var,$temp,$res))
            return $temp;

           preg_match($var,$temp,$res);
        //dd($res[0]);
        $prepare_data = str_ireplace($res[0],'place_holder',$temp);

         // dd($prepare_data);
      
          $final_data = preg_replace('#place_holder#',$data."\r\n".$res[2],$prepare_data);
          //
         //   dd($final_data);

          return $final_data;
       
    }

    public function withinHardCompile($data,$temp,array $expeission)
    { 
      
        $var = "#(<$expeission[0] (.*?)class=\"$expeission[1]\s?(.*?)\">)([\w\W]*?)<\/$expeission[0]>#";
           if(!preg_match($var,$temp,$res))
            return $temp;

           preg_match($var,$temp,$res);
        //dd($res[3]);
           $is_empty =trim($res[4]);
           if(empty($is_empty))
           {
            //dd($res);
               $prepare_data = str_ireplace($res[1],'place_holder',$temp);

              // dd($prepare_data);
      
              $final_data = preg_replace('#place_holder#',$res[1]."\r\n".$data."\r\n",$prepare_data);
          //
          // dd($final_data);

              return $final_data;

           }
        $prepare_data = str_ireplace($res[4],'place_holder',$temp);

         // dd($prepare_data);
      
          $final_data = preg_replace('#place_holder#',"\r\n".$data."\r\n",$prepare_data);
          //
           //dd($final_data);

          return $final_data;
       
    }

    public function deleteCasheBeforeHardCompile($view_name)
    {
        
         // dd($view_name);
         
         return @unlink($view_name);

    }

    public function CompileWhile($capt)
    {
        return '<?php while('.$capt[1].'):?>';
    }

    public function CompileEndWhile($capt)
    {
        return "<?php endwhile;?>";
    }

    public function compileContinue($capt)
    {
    	 return "<?php continue;?>";
    }

     

}