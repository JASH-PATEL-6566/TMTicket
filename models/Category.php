<?php
// /models/Category.php
namespace Models;

use Core\Database;
use PDOException;

class Category
{
    /**
     * Find a category by ID
     *
     * @param int $id Category ID
     * @return array|null Category data or null if not found
     */
    public static function find($id)
    {
        try {
            $stmt = Database::connect()->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error finding category: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all categories
     *
     * @return array Categories
     */
    public static function all()
    {
        try {
            $stmt = Database::connect()->query("SELECT * FROM categories ORDER BY name");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting categories: " . $e->getMessage());
            return [];
        }
    }
}
