<?php
// Admin - Review Moderation Actions (Feature 2.5)
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/validation.php';

initSession();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../public/login.php');
    exit;
}

$pdo = getDBConnection();

// Get and validate parameters
$review_id = sanitizeID($_GET['id'] ?? 0);
$action = sanitizeString($_GET['action'] ?? '');

if (!$review_id || !$action) {
    setFlashMessage('Invalid request', 'error');
    header('Location: index.php');
    exit;
}

// Fetch review
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE review_id = :id");
$stmt->execute([':id' => $review_id]);
$review = $stmt->fetch();

if (!$review) {
    setFlashMessage('Review not found', 'error');
    header('Location: index.php');
    exit;
}

try {
    switch ($action) {
        case 'approve':
            // Approve review
            $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE review_id = :id");
            $stmt->execute([':id' => $review_id]);
            setFlashMessage('Review approved successfully', 'success');
            break;
            
        case 'unapprove':
            // Unapprove review
            $stmt = $pdo->prepare("UPDATE reviews SET is_approved = 0 WHERE review_id = :id");
            $stmt->execute([':id' => $review_id]);
            setFlashMessage('Review unapproved', 'success');
            break;
            
        case 'disemvowel':
            // Disemvowel - remove vowels from text (moderation technique)
            $disemvoweled = preg_replace('/[aeiouAEIOU]/', '', $review['review_text']);
            $stmt = $pdo->prepare("UPDATE reviews SET review_text = :text WHERE review_id = :id");
            $stmt->execute([
                ':text' => $disemvoweled,
                ':id' => $review_id
            ]);
            setFlashMessage('Review disemvoweled (vowels removed)', 'success');
            break;
            
        case 'delete':
            // Delete review permanently
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = :id");
            $stmt->execute([':id' => $review_id]);
            setFlashMessage('Review deleted permanently', 'success');
            break;
            
        default:
            setFlashMessage('Invalid action', 'error');
    }
    
} catch (PDOException $e) {
    setFlashMessage('Error: ' . $e->getMessage(), 'error');
}

// Redirect back to reviews list
header('Location: index.php');
exit;
?>