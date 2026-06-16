<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
    });

    // Metodos get y post para los doctores

    // Get
    $app->get('/doctores', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $stmt = $db->query("SELECT * FROM Doctores");
        $doctores = $stmt->fetchAll();
        $response->getBody()->write(json_encode($doctores));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Post
    $app->post('/doctores', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        // getParsedBody() funciona porque addBodyParsingMiddleware() ya procesó el JSON
        $data = $request->getParsedBody();

        // Si por alguna razón no lo procesó, leer manualmente
        if (!$data) {
            $rawBody = $request->getBody()->getContents();
            $data = json_decode($rawBody, true);
        }

        if (!$data) {
            $response->getBody()->write(json_encode(["error" => "JSON inválido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $db->prepare("INSERT INTO Doctores 
            (IdDoctor, NombresDoctor, ApellidosDoctor, Especialidad, TurnoAtencion, PacientesMinDiarios, Sueldo, IdHospital) 
            VALUES (:IdDoctor, :NombresDoctor, :ApellidosDoctor, :Especialidad, :TurnoAtencion, :PacientesMinDiarios, :Sueldo, :IdHospital)");

        $stmt->execute([
            'IdDoctor'            => $data['IdDoctor'],
            'NombresDoctor'       => $data['NombresDoctor'],
            'ApellidosDoctor'     => $data['ApellidosDoctor'],
            'Especialidad'        => $data['Especialidad'],
            'TurnoAtencion'       => $data['TurnoAtencion'],
            'PacientesMinDiarios' => $data['PacientesMinDiarios'],
            'Sueldo'              => $data['Sueldo'],
            'IdHospital'          => $data['IdHospital']
        ]);

        $response->getBody()->write(json_encode(["mensaje" => "Doctor insertado correctamente"]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Metodso get y post para los hospitales

    // Get para un solo hospital
    $app->get('/hospitales/{id}', function (Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);
        $stmt = $db->prepare("SELECT * FROM Hospitales WHERE IdHospital = :id");
        $stmt->execute(['id' => $args['id']]);
        $hospital = $stmt->fetch();

        $response->getBody()->write(json_encode($hospital));
        return $response->withHeader('Content-Type', 'application/json');
    });

    //Get para todos los hospitales
    $app->get('/hospitales', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $stmt = $db->query("SELECT * FROM Hospitales");
        $hospitales = $stmt->fetchAll();
        $response->getBody()->write(json_encode($hospitales));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Post
    $app->post('/hospitales', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);

        $rawBody = $request->getBody()->getContents();
        $data = json_decode($rawBody, true);

        if ($data === null) {
            $response->getBody()->write(json_encode(["error" => "JSON inválido"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $stmt = $db->prepare("INSERT INTO Hospitales 
            (IdHospital, NomHospital, CapacidadAtencion, Especialidades) 
            VALUES (:IdHospital, :NomHospital, :CapacidadAtencion, :Especialidades)");

        $stmt->execute([
            'IdHospital'        => $data['IdHospital'],
            'NomHospital'       => $data['NomHospital'],
            'CapacidadAtencion' => $data['CapacidadAtencion'],
            'Especialidades'    => $data['Especialidades']
        ]);

        $response->getBody()->write(json_encode(["mensaje" => "Hospital insertado correctamente"]));
        return $response->withHeader('Content-Type', 'application/json');
        });
    };