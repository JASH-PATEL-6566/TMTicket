<?php
// /controllers/TicketController.php
namespace Controllers;

use Models\Ticket;
use Models\Category;
use Core\Response;

class TicketController
{
    /**
     * Handle the first step of ticket creation
     * Creates a ticket with mobile_no, category_id, and status=incomplete
     */
    public static function step1()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate input
            if (!isset($input['mobile_no']) || !isset($input['category_id'])) {
                return Response::error('Mobile number and category ID are required');
            }

            // Validate mobile number format
            if (!preg_match('/^[0-9]{10,15}$/', $input['mobile_no'])) {
                return Response::error('Invalid mobile number format');
            }

            // Check if category exists
            $category = Category::find($input['category_id']);
            if (!$category) {
                return Response::error('Category not found', 404);
            }

            // Store step 1
            $ticket_id = Ticket::storeStep($input['mobile_no'], $input['category_id']);

            if ($ticket_id) {
                return Response::json([
                    'message' => 'Step 1 saved',
                    'next_step' => 2,
                    'ticket_id' => $ticket_id
                ]);
            } else {
                return Response::error('Failed to save step 1');
            }
        } catch (\Exception $e) {
            error_log("Error in step1: " . $e->getMessage());
            return Response::error('An error occurred while processing your request');
        }
    }

    /**
     * Handle the second step of ticket creation
     * Updates the ticket with customer name
     */
    public static function step2()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate input
            if (!isset($input['ticket_id']) || !isset($input['name'])) {
                return Response::error('Ticket ID and name are required');
            }

            // Validate name (not empty)
            if (empty(trim($input['name']))) {
                return Response::error('Name cannot be empty');
            }

            // Update with name
            $success = Ticket::updateStep($input['ticket_id'], $input['name']);

            if ($success) {
                return Response::json(['message' => 'Step 2 saved', 'next_step' => 3]);
            } else {
                return Response::error('Failed to save step 2. Make sure you completed step 1 first.');
            }
        } catch (\Exception $e) {
            error_log("Error in step2: " . $e->getMessage());
            return Response::error('An error occurred while processing your request');
        }
    }


    /**
     * Handle the final step of ticket creation
     * Updates the ticket with order_id or rep_details and changes status to pending
     */
    public static function step3()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            // Validate input
            if (!isset($input['ticket_id']) || !isset($input['value'])) {
                return Response::error('Ticket ID and value are required');
            }

            // Fetch category based on ticket_id (Assuming Ticket::getCategoryByTicketId method exists)
            $category = Ticket::getCategoryByTicketId($input['ticket_id']);


            // Check if the category's slug is "issue-with-sales-rep"
            if ($category && trim($category['slug']) === "issue-with-sales-rep") {
                // Store value in rep_details
                $success = Ticket::finishStep($input['ticket_id'], 'rep_details', $input['value']);
            } else {
                // Store value in order_id
                $success = Ticket::finishStep($input['ticket_id'], 'order_id', $input['value']);
            }

            if ($success) {
                return Response::json([
                    'message' => 'Ticket created successfully',
                    'ticket_id' => $input['ticket_id']
                ]);
            } else {
                return Response::error('Failed to complete ticket. Make sure you completed steps 1 and 2 first.');
            }
        } catch (\Exception $e) {
            error_log("Error in step3: " . $e->getMessage());
            return Response::error('An error occurred while processing your request');
        }
    }
}
