@spread('lay')

@section('content')

[comment]define var[/comment]

{%name='hallo'} 

[comment]hard compiling another view[/comment]

@hardcompile(test[] within h1:title data:"echo mt_rand(1,1000)")

[comment]if statment[/comment]

{%if $name%} 
  {%print_r($_SERVER)%}
{%endif%}

~ob 
[comment]
testing new role function it will replace word (~ob) with predified callback
[/comment]

@endsection