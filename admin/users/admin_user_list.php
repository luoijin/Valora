    <?php
    // admin/users/admin_user_list.php - List all users with status
    session_start();

    // IMPORTANT: Only check session variables
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../../pages/login.php');
        exit();
    }

    require_once '../../config.php';
    require_once '../../dao/user_dao.php';

    $userDAO = new UserDAO($db);
    // Get all users ordered by ID
    $query = "SELECT * FROM users ORDER BY id ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $message = '';
    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message']);
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Users - Valora Admin</title>
        <link rel="stylesheet" href="../../assets/css/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        
    </head>
    <body>
        <div class="admin-container">
            <!-- Mobile Overlay -->
            <div class="mobile-overlay" id="mobileOverlay"></div>
            
            <!-- Sidebar -->
            <?php include __DIR__ . '/../includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="container">
                    <?php if (!empty($message)): ?>
                        <div class="message"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <div class="header">
                        <h1>User Management</h1>
                        <a href="admin_user_create.php" class="btn">
                            <i class="fas fa-user-plus"></i> Add New User
                        </a>
                    </div>
                    
                    <div class="content-section">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td>
                                        <span style="color: #1e3a8a; font-weight: 600;">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $user['status'] ?? 'active';
                                        $statusClass = $status === 'active' ? 'status-active' : 'status-inactive';
                                        ?>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($status); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="action-cell">
                                        <?php 
                                        $status = $user['status'] ?? 'active';
                                        if ($status === 'active'): 
                                        ?>
                                            <a href="admin_user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button onclick="openAuthModal(<?php echo $user['id']; ?>, 'deactivate', '<?php echo htmlspecialchars($user['username']); ?>')" 
                                                class="btn btn-danger btn-sm">
                                                    <i class="fas fa-user-times"></i> Remove
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button onclick="openAuthModal(<?php echo $user['id']; ?>, 'activate', '<?php echo htmlspecialchars($user['username']); ?>')" 
                                            class="btn btn-success btn-sm">
                                                <i class="fas fa-user-check"></i> Activate
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (empty($users)): ?>
                            <p style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fas fa-users" style="font-size: 48px; display: block; margin-bottom: 16px; opacity: 0.3;"></i>
                                No users found.
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authentication Modal -->
        <div class="modal-overlay" id="authModal">
            <div class="modal">
                <div class="modal-header">
                    <div class="modal-icon" id="modalIcon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="modal-title">
                        <h2 id="modalTitle">Confirm Action</h2>
                        <p id="modalSubtitle">Please verify your credentials</p>
                    </div>
                </div>
                
                <div class="modal-body">
                    <div class="modal-error" id="modalError"></div>
                    
                    <form id="authForm">
                        <input type="hidden" id="targetUserId" name="user_id">
                        <input type="hidden" id="actionType" name="action">
                        
                        <div class="form-group">
                            <label for="adminUsername">Username</label>
                            <input type="text" id="adminUsername" name="admin_username" required autocomplete="username">
                        </div>
                        
                        <div class="form-group">
                            <label for="adminPassword">Password</label>
                            <input type="password" id="adminPassword" name="admin_password" required autocomplete="current-password">
                        </div>
                    </form>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" onclick="closeAuthModal()">Cancel</button>
                    <button type="button" class="btn" id="confirmBtn" onclick="submitAuth()">Confirm</button>
                </div>
            </div>
        </div>
        
        <script>
            

            // Modal Functions
            function openAuthModal(userId, action, username) {
                const modal = document.getElementById('authModal');
                const modalIcon = document.getElementById('modalIcon');
                const modalTitle = document.getElementById('modalTitle');
                const modalSubtitle = document.getElementById('modalSubtitle');
                const confirmBtn = document.getElementById('confirmBtn');
                const modalError = document.getElementById('modalError');
                
                // Reset form and error
                document.getElementById('authForm').reset();
                modalError.classList.remove('active');
                
                // Set form data
                document.getElementById('targetUserId').value = userId;
                document.getElementById('actionType').value = action;
                
                // Configure modal based on action
                if (action === 'deactivate') {
                    modalIcon.className = 'modal-icon danger';
                    modalIcon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                    modalTitle.textContent = 'Deactivate User';
                    modalSubtitle.textContent = `You are about to deactivate "${username}"`;
                    confirmBtn.className = 'btn btn-danger';
                    confirmBtn.innerHTML = '<i class="fas fa-user-times"></i> Deactivate';
                } else {
                    modalIcon.className = 'modal-icon success';
                    modalIcon.innerHTML = '<i class="fas fa-check-circle"></i>';
                    modalTitle.textContent = 'Activate User';
                    modalSubtitle.textContent = `You are about to activate "${username}"`;
                    confirmBtn.className = 'btn btn-success';
                    confirmBtn.innerHTML = '<i class="fas fa-user-check"></i> Activate';
                }
                
                modal.classList.add('active');
            }

            function closeAuthModal() {
                const modal = document.getElementById('authModal');
                modal.classList.remove('active');
            }

            function submitAuth() {
                const form = document.getElementById('authForm');
                const userId = document.getElementById('targetUserId').value;
                const action = document.getElementById('actionType').value;
                const username = document.getElementById('adminUsername').value;
                const password = document.getElementById('adminPassword').value;
                const modalError = document.getElementById('modalError');
                
                if (!username || !password) {
                    modalError.textContent = 'Please enter both username and password';
                    modalError.classList.add('active');
                    return;
                }

                // Create form data
                const formData = new FormData();
                formData.append('user_id', userId);
                formData.append('action', action);
                formData.append('admin_username', username);
                formData.append('admin_password', password);

                // Submit to backend
                fetch('admin_user_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'admin_user_list.php?message=' + encodeURIComponent(data.message);
                    } else {
                        modalError.textContent = data.message || 'Authentication failed';
                        modalError.classList.add('active');
                    }
                })
                .catch(error => {
                    modalError.textContent = 'An error occurred. Please try again.';
                    modalError.classList.add('active');
                });
            }

            // Close modal on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAuthModal();
                }
            });

            // Close modal when clicking outside
            document.getElementById('authModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAuthModal();
                }
            });
        </script>
    </body>
    </html>