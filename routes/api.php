<?php

use App\Exceptions\InsufficientCreditsException;
use App\Domain\Waste\Http\Controllers\CollectTaskController;
use Illuminate\Support\Facades\Route;
use League\Config\Exception\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

Route::post("/collect-tasks/pre-execution-check", [
    CollectTaskController::class,
    "preExecutionCheck",
]);

Route::get("/test-404", function () {
    throw new NotFoundHttpException("This is a forced 404 for testing");
});

Route::get("/test-403", function () {
    throw new AccessDeniedHttpException("You shall not pass");
});

Route::get("/test-500", function () {
    throw new Exception("Something exploded internally");
});

Route::get("/domain-error", function () {
    throw new InsufficientCreditsException();
});

// Route::get("/validation-error", function () {
//     // Dados de exemplo que viriam da request
//     $dados = [
//         'email' => 'email-invalido',
//         'idade' => 15,
//         'nome' => '' // vazio para testar required
//     ];
    
//     // Validação super simples
//     $validator = validator($dados, [
//         'email' => 'required|email',
//         'idade' => 'required|integer|min:18',
//         'nome' => 'required|string|min:3'
//     ]);
    
//     if ($validator->fails()) {
//         // Retorna erro 422 com os detalhes da validação
//         return response()->json([
//             'success' => false,
//             'message' => 'Erro de validação',
//             'errors' => $validator->errors()
//         ], 422);
//     }
    
//     return response()->json([
//         'success' => true,
//         'message' => 'Dados válidos!'
//     ]);
// });
