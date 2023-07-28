<?php

 function _esc($string)
 {
         return htmlspecialchars($string??'',ENT_QUOTES);
 }
