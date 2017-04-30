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
use Silex\Application\TwigTrait;
use Application\UrlGeneratorTrait;
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

$app->post('/registro', function(Request $request) use($app){
    $regcli = $app['db']->insert('Clientes', array(
        'id_Cliente' => $request->get('id_cliente'),
        'Clave' => $request->get('clave'),
        'Nombre' => $request->get('nombre'),
        'Apellido'=> $request->get('apellidos'),
        'Correo'=> $request->get('correo'),
        'Calle'=> $request->get('calle'),
        'Colonia'=> $request->get('colonia'),
        'CP'=> $request->get('cp'),
        'Ciudad'=> $request->get('ciudad'),
        'Pais'=> $request->get('pais'),
        'Telefono'=> $request->get('telefono')
    ));

    if( $regcli <= 0 )
    {
        $app['session']->setFlash('error', 'Se ha producido un error al insertar el elemento');
        
        return $app['db']->render('cliente.twig.html', array(
            'id_cliente' => $request->get('id_Cliente'),
            'clave' => $request->get('Clave'),
            'nombre' => $request->get('Nombre'),
            'apellidos'=> $request->get('Apellido'),
            'correo'=> $request->get('Correo'),
            'calle'=> $request->get('Calle'),
            'colonia'=> $request->get('Colonia'),
            'cp'=> $request->get('CP'),
            'ciudad'=> $request->get('Ciudad'),
            'pais'=> $request->get('Pais'),
            'telefono'=> $request->get('Telefono')));
    }
    else
    {
        // Investigar sobre session, setFlash
        $app['session']->setFlash('ok', 'Elemento insertado correctamente');
        
        return $app->redirect($app['url_generator']->generate('registrar'));
    }

})->bind('registrar_item');

$app->get('/productos/', function () use ($app) {
    /*if (null === $user = $app['session']->get('user')) {
        return $app->redirect($app['url_generator']->generate('login_vista'));
    }*/
    $sql = "SELECT sku, Categoria_id, Nombre_Producto, Precio, url_imagen_grande FROM Productos";
    $post_productos = $app['db']->fetchAll($sql, array());
    
    return  $app['twig']->render('productos.html.twig', array(
        'post_productos'=>$post_productos
        ));
})
->bind('productos_vista')
;

$app->get('/productos/:sku/', function () use ($app) {
    /*if (null === $user = $app['session']->get('user')) {
        return $app->redirect($app['url_generator']->generate('login_vista'));
    }*/
    $sql = "SELECT sku, Categoria_id, Nombre_Producto, Descripcion, Precio, url_imagen FROM Productos";
    $post_productos = $app['db']->fetchAll($sql, array());
    
    return  $app['twig']->render('productos_sku.html.twig', array(
        'post_productos'=>$post_productos
        ));
})
->bind('productos_sku')
;

$app->post('/agregar/:sku', function(Request $request) use($app){

})->bind('agregar_item')
;
/*$app->get('/productos/:sku', function (Request $request) use($app){
    return "Detalles: Programar detalles del producto!";

    $sku = $request->get('pp.sku');
    $sql ="SELECT sku, Nombre_Producto, Descripcion, Precio, Categoria_id FROM Productos Where sku=?";
    $post = $app['db']->fetchAssoc($sql, array($sku));
})->bind('producto_detalle');*/

/*$app->get('/login', function(Request $request) use($app){

})->bind('login_vista');*/

$app->get('/login', function (Request $request) use ($app) {
    $username = $request->server->get('PHP_AUTH_USER', false);
    $password = $request->server->get('PHP_AUTH_PW');
 
    $sql = "SELECT * FROM Clientes WHERE correo = ?";
    $post = $app['db']->fetchAssoc($sql, array(strtolower($username)));
    
    if ($post['Correo'] === $username && $post['Clave'] === $password) {
        $app['session']->set('user', array('username' => $username));
        return $app->redirect($app['url_generator']->generate('productos_vista'));
    }

    $response = new Response();
    $response->headers->set('WWW-Authenticate', sprintf('Basic realm="%s"', 'site_login'));
    $response->setStatusCode(401, 'Please sign in.');
    return $response;
})->bind('login_vista');

$app->get('/account', function () use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    return "Welcome {$user['username']}!";
});

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
