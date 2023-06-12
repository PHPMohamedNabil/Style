# Style

By Mohamed Nabil (https://github.com/PHPMohamedNabil/)!

[Style]
a tiny PHP Template Engine you can use for small projects or educational purposes.

it loads HTML template to separate the presentation from the logic.

Features
--------
* Simple compiling tags *{$variable}*, *{#constant}*, @include(), *{%loop $data%}*, *{%if%}*, *[* comment *]*,, *{%func echo str_len($string)%}*
* Very Easy template Compiling as one class called Style just loads the full template and compiling it.
* [new feature] (Hardcompiling Templates) to send data to other template file so that be injected into other template in every page load.
* Easy to inject a new experissions fell free to add as many as you want.
* Secure when printing variables , as its filtered against  xss attacks.

Installation / Usage
--------------------

1. Install composer https://github.com/composer/composer
2. Create a composer.json inside your application folder:

    ``` composer require php-mohamed-nabil/style ```

Create a Style instance by passing it the folder(s) where your view files are located, and a cache folder. Render a template by calling the render method.

```php
use Style\Style;

$style = new Style('template/','template/temp/');

$style->render('page_sections',[]);
```
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

You can also use extend views and using @spread(parent_view_name)

```html
@spread('layout')
```
using also @sections @yield to send data from child to parent view

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

## Hard compiling Feature

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

@hardcompile(test['name'=>$name,$title] befire h1:title data:"echo mt_rand(1,1000)")
```

## Include view : get other view included in view page
```html
@include('main',['data'=>$data])
```
 Licence
-------

published under the MIT Licence.
