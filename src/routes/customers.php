<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Valitron\Validator as validate;

$app = new \Slim\App;

// Get all customers
$app->get('/api/customers', function (Request $request, Response $response, array $args) {
  $customers = DB::select('customers', "*")
    ->fetchAll();
  $data = json_encode($customers);
  echo $data;
});

// Get single customer
$app->get('/api/customers/{id}', function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  $customer = DB::select_where('customers', '*', ['id' => $id])
    ->fetch();
  $data = json_encode($customer);
  echo $data;
});


// Add a customer
$app->post('/api/customers', function (Request $request, Response $response, array $args) {
  $data = $request->getParsedBody();

  $v = new validate($data);
  $v->rule('required', ['first_name', 'last_name', 'phone', 'email', 'address', 'city', 'state'])
    ->rule('email', 'email');
  if ($v->validate()) {
    DB::insert('customers', $data);
    echo json_encode(['status' => true, 'message' => "Customer added", 'data' => $data]);
  } else {
    echo json_encode(['status' => false, 'errors' => $v->errors()]);
  }
});

// Update a customer
$app->put('/api/customers/{id}', function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  $data = $request->getParsedBody();
  DB::update('customers', ['id' => $id], $data);

  $customer = DB::select_where('customers', '*', ['id' => $id])
    ->fetch();
  echo json_encode(['status' => true, 'message' => "Customer updated", 'data' => $customer]);
});


// Delete a customer
$app->delete('/api/customers/{id}', function (Request $request, Response $response, array $args) {
  $id = $request->getAttribute('id');
  $customer = DB::select_where('customers', '*', ['id' => $id])
    ->fetch();
  $deleted = DB::delete('customers', ['id' => $id]);

  if ($deleted) {
    echo json_encode(['status' => true, 'message' => "Customer deleted", 'data' => $customer]);
  } else {
    echo json_encode(['status' => false, 'errors' => ['general_error' => 'Unable to delete']]);
  }
});
