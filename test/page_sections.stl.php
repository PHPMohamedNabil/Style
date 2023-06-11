@spread('layout')

@section('content')

<center>
    <h1>{$name}</h1>
</center>

<!--Note for (hardcompile):check the expressions template the value within h2 changed in every time this page loads by random number-->

<!-- you can pass view data after view template in the array or keep it empty if there is no data -->


 @hardcompile(expressions[] within h2:title data:"echo mt_rand(1,500);")


@endsection 