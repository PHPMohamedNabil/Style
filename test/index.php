<?php


use Style\Style;



//view you want to compile must has .stl at the end of it is name

$style = new Style('template/','template/temp/');

// add new template role using the below callback function that has the full experssion in $capt var

$style->addTempRole('test','\~ob',function($capt){
	  return $capt[0].'  ppppppppppppppppppoboobobo';
});

//render view

$style->render('page_sections',[]);