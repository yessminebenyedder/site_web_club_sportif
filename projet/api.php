<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

$useDatabase = false;   
$pdo = null;

if ($useDatabase) {
    $host = 'localhost';
    $dbname = 'projetweb';
    $user = 'root';
    $password = '';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Base de données inaccessible: ' . $e->getMessage()]);
        exit;
    }
} else {
   
    if (!isset($_SESSION['demo_users']))       $_SESSION['demo_users']       = [];
    if (!isset($_SESSION['demo_abonnements'])) $_SESSION['demo_abonnements'] = [];
    if (!isset($_SESSION['demo_paiements']))   $_SESSION['demo_paiements']   = [];
}

$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if ($route === 'register' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['error' => 'JSON invalide']);
        exit;
    }

    $email         = trim($data['email'] ?? '');
    $nom           = trim($data['nom'] ?? '');
    $prenom        = trim($data['prenom'] ?? '');
    $telephone     = trim($data['telephone'] ?? '');
    $club          = trim($data['club'] ?? '');
    $offre         = trim($data['offre'] ?? '');
    $montant       = floatval($data['montant'] ?? 0);
    $mode_paiement = trim($data['mode_paiement'] ?? 'carte');
    $duree_mois    = intval($data['duree_mois'] ?? 1);

    if (!$email || !$nom || !$prenom || !$club || !$offre) {
        http_response_code(400);
        echo json_encode(['error' => 'Champs manquants (email, nom, prénom, club, offre)']);
        exit;
    }

    $temp_password = bin2hex(random_bytes(4)); 

    if ($useDatabase) {
        
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Cet email est déjà utilisé']);
            exit;
        }

        $hash = password_hash($temp_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (email, mot_de_passe, nom, prenom, telephone, club_choisi, offre_choisie) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$email, $hash, $nom, $prenom, $telephone, $club, $offre]);
        $userId = $pdo->lastInsertId();

        $date_debut = date('Y-m-d');
        $date_fin   = date('Y-m-d', strtotime("+{$duree_mois} months"));
        $stmt = $pdo->prepare("INSERT INTO abonnements (utilisateur_id, offre_code, club_nom, date_debut, date_fin, montant_total, mode_paiement) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$userId, $offre, $club, $date_debut, $date_fin, $montant, $mode_paiement]);
        $aboId = $pdo->lastInsertId();

        $reference = 'PAY_' . strtoupper(uniqid());
        $stmt = $pdo->prepare("INSERT INTO paiements (utilisateur_id, montant, mode_paiement, reference, abonnement_id) VALUES (?,?,?,?,?)");
        $stmt->execute([$userId, $montant, $mode_paiement, $reference, $aboId]);

        $_SESSION['user_id']    = $userId;
        $_SESSION['user_email'] = $email;

        echo json_encode(['success' => true, 'user_id' => $userId, 'temp_password' => $temp_password]);
        exit;
    } else {
        
        foreach ($_SESSION['demo_users'] as $u) {
            if ($u['email'] === $email) {
                http_response_code(409);
                echo json_encode(['error' => 'Email déjà utilisé']);
                exit;
            }
        }
        $userId = count($_SESSION['demo_users']) + 1;
        $_SESSION['demo_users'][$userId] = [
            'id' => $userId, 'email' => $email, 'nom' => $nom, 'prenom' => $prenom,
            'telephone' => $telephone, 'club_choisi' => $club, 'offre_choisie' => $offre,
            'membre_depuis' => date('F Y')
        ];
        $aboId = count($_SESSION['demo_abonnements']) + 1;
        $_SESSION['demo_abonnements'][$aboId] = [
            'utilisateur_id' => $userId, 'offre_code' => $offre, 'club_nom' => $club,
            'date_debut' => date('Y-m-d'), 'date_fin' => date('Y-m-d', strtotime("+{$duree_mois} months")),
            'montant_total' => $montant, 'mode_paiement' => $mode_paiement, 'statut' => 'actif'
        ];
        $_SESSION['demo_paiements'][] = [
            'utilisateur_id' => $userId, 'montant' => $montant, 'mode_paiement' => $mode_paiement,
            'date_paiement' => date('Y-m-d'), 'reference' => 'DEMO_' . uniqid()
        ];
        $_SESSION['user_id'] = $userId;
        echo json_encode(['success' => true, 'user_id' => $userId, 'temp_password' => $temp_password]);
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'Route inconnue. Utilisez ?route=register']);
?>