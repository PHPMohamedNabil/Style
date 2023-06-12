<?php

use Style\View;



//die(var_dump(View::getInstance()->tempdir));

//view you want to compile must has .stl at the end of it is name

View::$dir='template/';

View::load('page_sections',['name'=>'Welcome to Test View']);
