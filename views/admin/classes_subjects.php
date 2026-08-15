<?php
$pageTitle = 'Manage Classes & Subjects | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
:root {
    --primary: #000;
    --accent: #f06724;
    --accent-purple: #7f2677;
    --accent-hover: #d95318;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
    --border-color: #e2e8f0;
}

.management-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
    width: 100%;
}

.card-box {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    padding: 20px;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.card-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.card-header-flex h3 {
    font-size: 1.1rem;
    color: var(--accent-purple);
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-primary-sm {
    background: var(--accent);
    color: #ffffff;
    border: none;
    padding: 10px 16px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: background 0.2s ease;
    min-height: 40px; 
}

.btn-primary-sm:hover {
    background: var(--accent-hover);
}

.action-btn {
    background: none;
    border: none;
    color: #555;
    cursor: pointer;
    padding: 8px;
    font-size: 1rem;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    min-height: 36px;
}

.action-btn:hover {
    color: var(--accent-purple);
    background-color: #f1f5f9;
}

.action-btn.delete:hover {
    color: #ef4444;
    background-color: #fee2e2;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 0.85rem;
    white-space: nowrap;
}

.data-table th,
.data-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
}

.data-table th {
    background: #f8fafc;
    font-weight: 600;
    color: #555;
}

.badge-status {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
}

.badge-active {
    background: #dcfce7;
    color: #15803d;
}

.badge-inactive {
    background: #fee2e2;
    color: #b91c1c;
}

.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1200;
    align-items: center;
    justify-content: center;
    padding: 16px;
    overflow-y: auto;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: #ffffff;
    width: 100%;
    max-width: 480px;
    border-radius: 10px;
    padding: 24px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.modal-header h4 {
    margin: 0;
    font-size: 1.1rem;
    color: var(--primary);
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 6px;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.9rem;
    box-sizing: border-box;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:hover {
    border-color: var(--accent);
}

.form-control:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(240, 103, 36, 0.15);
}

@media (max-width: 1024px) {
    .management-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

@media (max-width: 576px) {
    .card-box {
        padding: 16px;
    }

    .card-header-flex {
        flex-direction: column;
        align-items: stretch;
    }

    .btn-primary-sm {
        width: 100%;
    }

    .modal-content {
        padding: 16px;
    }
}
</style>

<div class="management-grid">
    <div class="card-box">
        <div class="card-header-flex">
            <h3><i class="fas fa-school"></i> Classes/Clubs</h3>
            <button class="btn-primary-sm" onclick="openModal('addClassModal')">
                <i class="fas fa-plus"></i> Add Class/Club
            </button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Level</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($classes)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No classes/clubs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($classes as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['level'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><strong><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td>
                                    <span class="badge-status <?= $c['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= $c['is_active'] ? 'Active' : 'Disabled' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn" onclick='editClass(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>/admin/classes/delete/<?= $c['id'] ?>" 
                                       class="action-btn delete" 
                                       onclick="return confirm('Delete class?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-box">
        <div class="card-header-flex">
            <h3><i class="fas fa-book"></i> Subjects</h3>
            <button class="btn-primary-sm" onclick="openModal('addSubjectModal')">
                <i class="fas fa-plus"></i> Assign Subject
            </button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No subjects found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subjects as $s): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8', false) ?></strong> 
                                    (<?= htmlspecialchars($s['code'] ?? 'N/A', ENT_QUOTES, 'UTF-8', false) ?>)
                                </td>
                                <td><?= htmlspecialchars($s['class_name'] ?? 'Unassigned', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?= htmlspecialchars(trim(($s['first_name'] ?? 'Unassigned') . ' ' . ($s['last_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <button class="action-btn" onclick='editSubject(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="<?= BASE_URL ?>/admin/subjects/delete/<?= $s['id'] ?>" 
                                       class="action-btn delete" 
                                       onclick="return confirm('Delete subject?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal" id="addClassModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Add New Class/Club</h4>
            <button class="action-btn" onclick="closeModal('addClassModal')">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/admin/classes/create" method="POST">
            <div class="form-group">
                <label>Class/Club Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Primary Seven" required>
            </div>
            <div class="form-group">
                <label>Level Code/Number</label>
                <input type="text" name="level" class="form-control" placeholder="e.g. P7 or 0 for Club" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked> Active Class/Club
                </label>
            </div>
            <button type="submit" class="btn-primary-sm" style="width: 100%;">Save Class/Club</button>
        </form>
    </div>
</div>

<div class="modal" id="editClassModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Edit Class/Club</h4>
            <button class="action-btn" onclick="closeModal('editClassModal')">&times;</button>
        </div>
        <form id="editClassForm" method="POST">
            <div class="form-group">
                <label>Class/Club Name</label>
                <input type="text" name="name" id="edit_class_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Level Code/Number</label>
                <input type="text" name="level" id="edit_class_level" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_class_description" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" id="edit_class_active" value="1"> Active Class
                </label>
            </div>
            <button type="submit" class="btn-primary-sm" style="width: 100%;">Update Class</button>
        </form>
    </div>
</div>

<div class="modal" id="addSubjectModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Add Subject to Class/Club</h4>
            <button class="action-btn" onclick="closeModal('addSubjectModal')">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>/admin/subjects/create" method="POST">
            <div class="form-group">
                <label>Assign to Class/Club</label>
                <select name="class_id" class="form-control" required>
                    <option value="">-- Select Class/Club --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Subject Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Mathematics" required>
            </div>
            <div class="form-group">
                <label>Subject Code</label>
                <input type="text" name="code" class="form-control" placeholder="e.g. MATH_P7">
            </div>
            <div class="form-group">
                <label>Assigned Teacher (Optional)</label>
                <select name="teacher_id" class="form-control">
                    <option value="">-- Select Teacher --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>">
                            <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked> Active Subject
                </label>
            </div>
            <button type="submit" class="btn-primary-sm" style="width: 100%;">Save Subject</button>
        </form>
    </div>
</div>

<div class="modal" id="editSubjectModal">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Edit Subject</h4>
            <button class="action-btn" onclick="closeModal('editSubjectModal')">&times;</button>
        </div>
        <form id="editSubjectForm" method="POST">
            <div class="form-group">
                <label>Assign to Class</label>
                <select name="class_id" id="edit_sub_class_id" class="form-control" required>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Subject Name</label>
                <input type="text" name="name" id="edit_sub_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Subject Code</label>
                <input type="text" name="code" id="edit_sub_code" class="form-control">
            </div>
            <div class="form-group">
                <label>Assigned Teacher</label>
                <select name="teacher_id" id="edit_sub_teacher_id" class="form-control">
                    <option value="">-- None --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>">
                            <?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" id="edit_sub_active" value="1"> Active Subject
                </label>
            </div>
            <button type="submit" class="btn-primary-sm" style="width: 100%;">Update Subject</button>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
};

function editClass(data) {
    document.getElementById('editClassForm').action = `<?= BASE_URL ?>/admin/classes/update/${data.id}`;
    document.getElementById('edit_class_name').value = data.name;
    document.getElementById('edit_class_level').value = data.level || '';
    document.getElementById('edit_class_description').value = data.description || '';
    document.getElementById('edit_class_active').checked = (data.is_active == 1);
    openModal('editClassModal');
}

function decodeHtml(html) {
    const txt = document.createElement("textarea");
    txt.innerHTML = html;
    return txt.value;
}

function editSubject(data) {
    document.getElementById('editSubjectForm').action = `<?= BASE_URL ?>/admin/subjects/update/${data.id}`;
    document.getElementById('edit_sub_class_id').value = data.class_id;
    document.getElementById('edit_sub_name').value = decodeHtml(data.name);
    document.getElementById('edit_sub_code').value = decodeHtml(data.code || '');
    document.getElementById('edit_sub_teacher_id').value = data.teacher_id || '';
    document.getElementById('edit_sub_active').checked = (data.is_active == 1);
    openModal('editSubjectModal');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>