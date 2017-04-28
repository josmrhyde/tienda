<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('homepage')
;

$app->get('/blog/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM clientes WHERE id_Cliente = $id";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));
    
    return  $app['twig']->render('datos.html.twig', array(
        'id_Cliente'=>$post['id_Cliente'],
        'clave'=>$post['Clave'],
        'nombre'=>$post['Nombre'],
        'apellido'=>$post['Apellido'],
        'correo'=>$post['Correo'],
        'calle'=>$post['Calle'],
        'colonia'=>$post['Colonia'],
        'cp'=>$post['CP'],
        'ciudad'=>$post['Ciudad'],
        'pais'=>$post['Pais'],
        'telefono'=>$post['Telefono'],
        'fecha_creacion'=>$post['Fecha_creacion']
        ));
})
->bind('blog_id')
;

$app->get('/registro', function() use ($app) {
    $sql = 'SELECT * FROM Clientes';
    $post_cliente = $app['db']->fetchAll($sql, array());

    return $app['twig']->render('cliente.html.twig', array());
})->bind('registrar');

$app->post('/registro', function() use($app){
    $regcli = $app['db']->insert('Clientes', array(
        'id_Cliente' => $app['request']->get('id_cliente'),
        'Clave' => 'dato',
        'Nombre' => 'nombre',
        'Apellido'=> 'apellidos',
        'Correo'=> 'correo',
        'Calle'=> 'calle',
        'Colonia'=> 'colonia',
        'CP'=> 'cp',
        'Ciudad'=> 'ciudad',
        'Pais'=> 'pais',
        'Telefono'=> 'telefono'
    ));

    if( $regcli <= 0 )
    {
        $app['session']->setFlash('error', 'Se ha producido un error al insertar el elemento');
        
        return $app['db']->render('cliente.twig.html', array(
            'id_cliente' => $app['request']->get('id_Cliente'),
            'clave' => $app['request']->get('Clave'),
            'nombre' => $app['request']->get('Nombre'),
            'apellidos'=> $app['request']->get('Apellido'),
            'correo'=>$app['request']->get('Correo'),
            'calle'=>$app['request']->get('Calle'),
            'colonia'=>$app['request']->get('Colonia'),
            'cp'=>$app['request']->get('CP'),
            'ciudad'=>$app['request']->get('Ciudad'),
            'pais'=>$app['request']->get('Pais'),
            'telefono'=>$app['request']->get('Telefono')));
    }
    else
    {
        $app['session']->setFlash('ok', 'Elemento insertado correctamente');
        
        return $app->redirect($app['url_generator']->generate('blog_id'));
    }

})->bind('registrar_item');

/*$app->get('/registro', function (Request $request) use ($app) {
    // some default data for when the form is displayed the first time

    $form = $app['form.factory']->createBuilder(FormType::class)
        ->add('IdCliente')
        ->add('Clave')
        ->add('Nombre')
        ->add('Apellidos')
        ->add('Correo')
        ->add('Calle')
        ->add('Colonia')
        ->add('CP')
        ->add('Ciudad')
        ->add('Pais')
        ->add('Telefono')
        /*->add('submit', SubmitType::class, [
            'label' => 'Save',
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();
        var_dump($data);
        die;
        // do something with the data

        // redirect somewhere
        //return $app->redirect('...');
    }

    // display the form
    //return $app['twig']->render('registro.html.twig', array('form' => $form->createView()));
    return $app['twig']->render('cliente.php', array());

    $app['dbs']['mysql_write']->executeQuery('INSERT INTO clientes (id_Cliente, Clave, Nombre, Apellido, Correo, Calle, Colonia, CP, Ciudad, Pais, Telefono) VALUES ($form.IdCliente, $form.Nombre, $form.Apellidos, $form.Correo, $form.Calle, $form.Colonia, $form.CP, $form.Ciudad, $form.Pais, $form.Telefono)');
})->method('GET|POST');*/

$app->get('/productos/', function () use ($app) {
    $sql = "SELECT Categoria_id, Nombre_Producto, Precio, url_imagen_grande FROM productos";
    $post_productos = $app['db']->fetchAll($sql, array());
    
    return  $app['twig']->render('productos.html.twig', array(
        'post_productos'=>$post_productos
        ));
})
->bind('productos_vista')
;

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
