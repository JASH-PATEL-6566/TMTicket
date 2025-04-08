<?php
// /models/Ticket.php
namespace Models;

use Core\Database;
use Core\Response;
use PDOException;

class Ticket
{
    /**
     * Initialize a new ticket (Step 1)
     *
     * @param string $mobile_no Mobile number
     * @param int $category_id Category ID
     * @return string|null Ticket ID or null on failure
     */
    public static function storeStep($mobile_no, $category_id)
    {
        try {
            // Generate a ticket ID
            $ticket_id = 'TKC-' . self::generateUuid();

            $pdo = Database::connect();

            // Check if there's already a ticket for this mobile number
            $checkStmt = $pdo->prepare("SELECT ticket_id FROM tickets WHERE mobile_no = ? AND status = 'incomplete'");
            $checkStmt->execute([$mobile_no]);
            $existing = $checkStmt->fetch();

            if ($existing) {
                // Update existing ticket
                $stmt = $pdo->prepare("UPDATE tickets SET category_id = ?, updated_at = NOW() WHERE mobile_no = ? AND status = 'incomplete'");
                $stmt->execute([$category_id, $mobile_no]);
                return $existing['ticket_id'];
            } else {
                // Create new ticket with incomplete status
                $stmt = $pdo->prepare("INSERT INTO tickets 
                    (ticket_id, mobile_no, category_id, status, created_by, created_at) 
                    VALUES (?, ?, ?, 'incomplete', 'by_bot', NOW())");
                $stmt->execute([$ticket_id, $mobile_no, $category_id]);
                return $ticket_id;
            }
        } catch (PDOException $e) {
            error_log("Error in step 1: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update ticket with customer name (Step 2)
     *
     * @param string $mobile_no Mobile number
     * @param string $name Customer name
     * @return bool Success status
     */
    public static function updateStep($ticket_id, $name)
    {
        try {
            $stmt = Database::connect()->prepare("UPDATE tickets SET name = ?, updated_at = NOW() WHERE ticket_id = ? AND status = 'incomplete'");
            $stmt->execute([$name, $ticket_id]);

            // Check if any row was affected
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error in step 2: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Complete ticket creation with order_id or rep_details (Step 3)
     *
     * @param string $mobile_no Mobile number
     * @param string $field Field name (order_id or rep_details)
     * @param mixed $value Field value
     * @return string|null Ticket ID or null on failure
     */
    public static function finishStep($ticket_id, $field, $value)
    {
        try {
            $pdo = Database::connect();

            // Get the incomplete ticket
            $stmt = $pdo->prepare("SELECT ticket_id FROM tickets WHERE ticket_id = ? AND status = 'incomplete'");
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch();

            if (!$ticket) {
                return null;
            }

            // Update with final details and set status to pending
            $updateFields = "status = 'pending', updated_at = NOW()";
            $params = [];

            if ($field === 'order_id') {
                $updateFields .= ", order_id = ?";
                $params[] = $value;
            } else if ($field === 'rep_details') {
                $updateFields .= ", rep_details = ?";
                $params[] = $value;
            }

            $params[] = $ticket_id;

            $updateStmt = $pdo->prepare("UPDATE tickets SET $updateFields WHERE ticket_id = ? AND status = 'incomplete'");
            $updateStmt->execute($params);

            return $ticket['ticket_id'];
        } catch (PDOException $e) {
            error_log("Error in step 3: " . $e->getMessage());
            return null;
        }
    }

    public static function getCategoryByTicketId($ticket_id)
    {
        try {
            // Get the database connection
            $pdo = Database::connect();

            // Prepare and execute the query to get the category_id from the ticket
            $stmt = $pdo->prepare("SELECT category_id FROM tickets WHERE ticket_id = ?");
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch();

            // Check if the ticket exists
            if (!$ticket) {
                return null;  // Return null if the ticket does not exist
            }

            // Get the category_id
            $category_id = $ticket['category_id'];

            // Prepare and execute the query to get the category details from the categories table
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $category = $stmt->fetch();

            // Check if the category exists
            if (!$category) {
                return null;  // Return null if the category does not exist
            }

            // Return the category data
            return $category;
        } catch (PDOException $e) {
            // Log the error
            error_log("Database error in getCategoryByTicketId: " . $e->getMessage());

            // Return null in case of error
            return null;
        }
    }

    /**
     * Generate a UUID v4
     * 
     * @return string UUID
     */
    private static function generateUuid()
    {
        // Generate a UUID v4 without requiring Symfony's component
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
