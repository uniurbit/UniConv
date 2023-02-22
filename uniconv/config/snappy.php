<?php

return array(


    'pdf' => array(
        'enabled' => true,
        'binary'  => str_replace("'", '"', env('WKTML_WINDOWS','/usr/local/bin/wkhtmltopdf')),  
        'timeout' => false,
        'options' => [
            'enable-local-file-access' => true,
        ],        
        'env'     => array(),
    ),
    'image' => array(
        'enabled' => true,
        'binary'  => '/usr/local/bin/wkhtmltoimage',
        'timeout' => false,
        'options' => array(),
        'env'     => array(),
    ),


);
