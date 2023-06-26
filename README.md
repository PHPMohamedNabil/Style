# Style

By Mohamed Nabil (https://github.com/PHPMohamedNabil/)!

[Style]
is lightweight a tiny PHP Template Engine you can use for small projects or educational purposes.

Feel the power of the template engines of big libraries in your code with simple and flexible usage and little code.

Features
--------
* Simple compiling tags *{$variable}*, *{#constant}*, @include(), *{%loop $data%}*, *{%if%}*, *[* comment *]*,, *{%func echo str_len($string)%}*
* Very Easy template Compiling as one class called Style just loads the full template and compiling it.
* [new feature] (Hardcompiling Templates) to send data to other template file so that be injected into other template in every page load.
* Easy to inject a new experissions fell free to add as many as you want.
* Secure when printing variables , as its filtered html content against some  xss attacks.

Table of contents
=================

<!--ts-->
   * [Installation](#installation)
   * [Usage](#usage)
      * [Custom Expressions](#custom-expressions)
      * [Sections](#sections)
      * [Hard Compiling](#hard-compiling-feature)
      * [Including View](#including-view)
      * [Foreach loop](#foreach-loop)
      * [Html Creation](#html-creation)
      * [Printing Vars](#printing-vars)
      * [Terminate the code](#terminate-the-code)
      * [Printing html Content](#printing-html-content)
      * [Table of expressions](#Expressions-of-statments)
   * [Licence](#licence)
<!--te-->


Installation
------------

1. Install composer https://github.com/composer/composer
2. Create a composer.json inside your application folder:

    ``` composer require php-mohamed-nabil/style ```
    
Usage
-----
Create a Style instance by passing it the folder where your view files are located, and a cache folder. Render a template by calling the render method.

```php
use Style\Style;

$style = new Style('template/','template/temp/');

$style->render('page_sections',[]);
```
## Custom-Expressions 
You can also add custom expressions using the `addTempRole()` function:

```php
$style->addTempRole('test','\~ob',function($capt){
	  return $capt[0].'  ppppppppppppppppppoboobobo';
});
$style->render('page_sections',[]);
```
Which allows you to use the following in your  template:

```
 here the ppppppppp : ~ob
```
## Sections
You can also use extend views and using @spread(parent_view_name)

```html
@spread('layout')
```
using also @sections @addsection to send data from child to parent view

```html
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>layout page</title>
</head>
<body>

<p class="yield">
    <!-- this will yield data and print it here form child view -->
	@addsection('content')
</p>

</html>
```
```html
@spread('layout')

<!-- add data to the main view and render show it -->
@section('content')
  My first paragraph in parent view 
@endsection
```

## Hard-compiling-Feature

you can now send data from one view to another one as it will be compiled and hardcoded example :
### in the view main you will write the below expression that when view main.stl.php page loaded or compilled
### The view test will be injected by random number in every main.stl.php page load within h1 tag that has class title
```html

@hardcompile(test[] within h1:title data:"echo mt_rand(1,1000)")
```
results in test.stl.php
```html

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Test</title>
</head>
<body>

<h1 class="title">
681 <!-- this a random number hardcoded by main view  -->
</h1>
    
```
### hard compilling can be (before | after | within) the specified tag in other view to be injected
you can send data to other view like this :
```html

@hardcompile(test['name'=>$name,$title] before h1:title data:"echo mt_rand(1,1000)")
```

## including-view 
get other view included in view page
```html
@display('main',['data'=>$data])
```
## foreach-loop 
in tempaltes 

```html
<div class="">
@foreach($users as $user)
  {$user->username}
@endforeach
</div>
```
## Html-Creation 
you can now create form with its input data 

```php
[php]
       print \Style\Style::form('/',[
        'method'=>'post',
        'enctype'=>'multipart/form-data',
        'id'=>'first-form'
   ])->formInput('username',['class'=>'form-control','type'=>'text'])->formInput('password',['class'=>'form-control','type'=>'password'])->formInput('file',['class'=>'form-input-file','type'=>'file'])->renderForm();

  [/php]
```
will output:
```html
<form action="/" method="post" enctype="multipart/form-data" id="first-form">

		<input name="username" class="form-control" type="text">

		<input name="password" class="form-control" type="password">

		<input name="file" class="form-input-file" type="file">

</form>
```
### Printing-Vars
```html
{$var_name}
```
## Terminate-the-code
of view like die
you can use @backwithfalse it is just converted to return false and exit from code any code or html after it will not be executed

## printing-html-content
without stopping entities
you can print html code witout escaping it the main reason of it if you want to show a post content or has a block of html code
to be appear and effected by browser  you can use {%$post%} as an expample:
```html
<div class="blog-post-content">
{%$posts->post_content%}
</div>
```
## Expressions-of-statments:
| Expression | Description |
| --- | --- |
| `{$var}` | for printing the variable var with **escaping against xss** |
| `{%$var%}` | printing var without escaping or filtering it , if it function it will be exacuted only not printed ex.:{%print_r($arr)%}  |
| `{%var='name'}` | define a variable inside the view :**$var='name'**|
| `{%func echo ucfirst($var)}` |execute the function or echo it **echo word is optional if you want to echo the function**|
| `[comment]ww [/comment]` | any thing in between it will not be compilled|
| `[php] var_dump($arr); [/php]` | write php code|
| `{%if $var>0%}` | define if statment|
| `{%else%},{%elseif%} and {%endif%}` | define else or elseif statment and you can use endif statment to end the statment|
| `@addsection($name)` |used in layout or parent view to implement section content that will be printed later in child view |
| `@spread($name)` | extend the parent view in the child view |
| `@section($name)` | start the section in child view |
| `@endsection($name)` | end the section in child view |
| `@foeach` | start the for each loop |
| `@endforeach` | end the for each loop |
| `@for()` | start the for  loop |
| `@endfor` | end the for  loop |
| `@while()` | start while statment |
| `@endwhile` |  end while statment |
| `@switch($var)` | start the switch statment |
| `@case($name)` | case condition inside switch statment |
| `@break` | break the statment or the loop |
| `@continue` | continue the statment or the loop |
| `@default` | default condition inside switch statment |
| `@backwithfalse` |  it is just converted to return false and exit from code any code or html after it will not be executed |
| `@hardcompile(view_name[] before\|after\|within tagname:classname data:"php_code_here")` | hard compiling other **view_name** and inject data content before or after or within tagname that has a classname this will send data to other view on every exacute of this experission |






 Licence
-------

published under the MIT Licence.
