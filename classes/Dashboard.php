<?php
/**
 * Clase Principal del Dashboard
 */

class Dashboard {
    /**
     * Obtiene estadísticas generales
     */
    public static function getStatistics() {
        $db = Database::getConnection();
        $stats = [];
        
        try {
            // Total de usuarios
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 1");
            $result = $stmt->fetch();
            $stats['users'] = $result['total'];
            
            // Total de administradores
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin' AND status = 1");
            $result = $stmt->fetch();
            $stats['admins'] = $result['total'];
            
            // Usuarios activos este mes
            $stmt = $db->query("SELECT COUNT(*) as total FROM users 
                                WHERE last_login >= DATEADD(month, -1, GETDATE())");
            $result = $stmt->fetch();
            $stats['active_users'] = $result['total'];
            
            // Usuarios nuevos este mes
            $stmt = $db->query("SELECT COUNT(*) as total FROM users 
                                WHERE created_at >= DATEADD(month, -1, GETDATE())");
            $result = $stmt->fetch();
            $stats['new_users'] = $result['total'];
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting statistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene datos para el gráfico de usuarios por mes
     */
    public static function getUsersByMonth() {
        $db = Database::getConnection();
        
        try {
            $stmt = $db->query("SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count 
                                FROM users 
                                GROUP BY YEAR(created_at), MONTH(created_at) 
                                ORDER BY year DESC, month DESC");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting users by month: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene los últimos usuarios registrados
     */
    public static function getRecentUsers($limit = 5) {
        $db = Database::getConnection();
        
        try {
            $stmt = $db->prepare("SELECT id, name, email, role, created_at 
                                  FROM users 
                                  ORDER BY created_at DESC 
                                  OFFSET 0 ROWS FETCH NEXT ? ROWS ONLY");
            $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
            //si se requieren mas parametros
            //$stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting recent users: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene usuarios por rol
     */
    public static function getUsersByRole() {
        $db = Database::getConnection();
        
        try {
            $stmt = $db->query("SELECT role, COUNT(*) as count 
                                FROM users 
                                GROUP BY role");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting users by role: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Realiza búsqueda de usuarios
     */
    public static function searchUsers($term, $page = 1, $limit = ITEMS_PER_PAGE) {
        $db = Database::getConnection();

        $offset = ($page - 1) * $limit;
        
                    try {
                    $stmt = $db->prepare("SELECT id, name, email, role, status, created_at ,last_login
                                        FROM users 
                                        WHERE name LIKE ? OR email LIKE ? 
                                        ORDER BY created_at DESC 
                                        OFFSET ? ROWS FETCH NEXT ? ROWS ONLY");

                    $searchTerm = "%$term%";
                    
                    // Vinculamos los strings normalmente
                    $stmt->bindValue(1, $searchTerm);
                    $stmt->bindValue(2, $searchTerm);
                    
                    // Forzamos que OFFSET y LIMIT sean tratados como enteros
                    $stmt->bindValue(3, (int)$offset, PDO::PARAM_INT);
                    $stmt->bindValue(4, (int)$limit, PDO::PARAM_INT);

                    $stmt->execute();
                    return $stmt->fetchAll();
                } catch (Exception $e) {
                    error_log("Error searching users: " . $e->getMessage());
                    return [];
                }
    }
    
    /**
     * Cuenta resultados de búsqueda
     */
    public static function countSearchResults($term) {
        $db = Database::getConnection();
        
        try {
            $stmt = $db->prepare("SELECT COUNT(*) as total 
                                  FROM users 
                                  WHERE name LIKE ? OR email LIKE ?");
            $searchTerm = "%$term%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $result = $stmt->fetch();
            return $result['total'];
        } catch (Exception $e) {
            error_log("Error counting search results: " . $e->getMessage());
            return 0;
        }
    }
}
?>