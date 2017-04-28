<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

//Request::setTrustedProxies(array('127.0.0.1'));


$curso ='Prueba de inicio';
$regCliente = array(
    1 => array(
        'id' => '1',
        'clave' => 'hemj791112',
        'nombre' => 'Jos',
        'apellido' => 'HM',
        'correo' => 'iherjos@comunidad.unam.mx',
        'calle' => 'Bilbao',
        'colonia' => 'San Nicolás',
        'cp' => '76521',
        'ciudad' => 'CDMX',
        'pais' => 'México',
        'telefono' => '5527212945',
        'fecha_creacion' => '18 Marzo 2017',
        'fecha_actualizacion' => '18 Marzo 2017',
        'titulo' => 'Usando Silex',
        'cuerpo' => 'pienso en ti'
    ),
);

$app->get('/registro', function() use ($regCliente){
    $output = '';
    foreach ($regCliente as $post) {
        $output .= $post ['nombre'];
        $output .= '<br/>';
        $output .= $post ['correo'];
        $output .= '<br/>';
        $output .= $post ['telefono'];
    }
    return $output;
});

$app->get('/', function () use ($app,$curso) {
    return $app['twig']->render('index.html.twig', array('curso' => $curso));
})
->bind('homepage')
;

$app->get('/demopage', function () use ($app,$curso) {
    return $app['twig']->render('index.html.twig', array('curso' => $curso));
})
->bind('demopage')
;

$app->get('/variables/{usuario}/{correo}', function ($usuario,$correo) use ($app) {
    return $app['twig']->render('variables.html.twig', array('usuario'=> $usuario . '|||correo electrónico: '. $correo));
})
->bind('variables')
;

/*
$app->get('/registro', function () use ($app) {
    return $app['twig']->render('registro.html.twig', array('usuario' => $usuario .'->'. $correo));
})
->bind('variables')
;
*/



$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});

/*
$app->match('/registro', function (Request $request) use ($app) {
    // algún dato predefinido para cuando se muestra el formulario por primera vez
    $data = array(
        'Nombre' => 'Your name',
        'email' => 'Your email',
    );

    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('Nombre')
        ->add('email')
        ->add('gender', 'choice', array(
            'choices' => array(1 => 'male', 2 => 'female'),
            'expanded' => true,
        ))
        ->getForm();

    if ('POST' == $request->getMethod()) {
        $form->bind($request);

        if ($form->isValid()) {
            $data = $form->getData();

            // hace algo con los datos

            // redirige a algún lugar
            return $app->redirect('...');
        }
    }

    // muestra el formulario
    return $app['twig']->render('registro.html.twig', array('form' => $form->createView()));
});
*/

$app->match('/form', function (Request $request) use ($app) {
    // some default data for when the form is displayed the first time
    $data = array(
        'name' => 'Your name',
        'email' => 'Your email',
    );
 
    $form = $app['form.factory']->createBuilder('form', $data)
        ->add('name')
        ->add('email')
        ->add('gender', 'choice', array(
            'choices' => array(1 => 'male', 2 => 'female'),
            'expanded' => true,
        ))
        ->getForm();
 
    $form->handleRequest($request);
 
    if ($form->isValid()) {
        $data = $form->getData();
 
        // hacer algo con los datos enviados
 
        // redirigir al usuario a algún otro sitio
        return $app->redirect('...');
    }
 
    // mostrar el formulario
    return $app['twig']->render('form.html.twig', array('form' => $form->createView()));
});