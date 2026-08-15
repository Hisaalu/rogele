<?php
$pageTitle = 'Manage Classes & Subjects | ROGELE';
require_once __DIR__ . '/../layouts/admin_header.php';
?>

<style>
.management-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

@media (max-width: 992px) {
    .management-grid {
        grid-template-columns: 1fr;
    }
}

.card-box {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: var(--shadow-sm);
    padding: 20px;
}

.card-header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.card-header-flex h3 {
    font-size: 1.1rem;
    color: var(--primary);
    font-weight: 700;
    margin: 0;
}

.btn-primary-sm {
    background: var(--accent);
    color: #ffffff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}

.btn-primary-sm:hover {
    background: var(--accent-hover);
}

.action-btn {
    background: none;
    border: none;
    color: #555;
    cursor: pointer;
    padding: 4px;
    font-size: 0.9rem;
}

.action-btn:hover {
    color: var(--primary);
}

.action-btn.delete:hover {
    color: #ef4444;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 0.85rem;
}

.data-table th,
.data-table td {
    padding: 10px;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
}

.data-table th {
    background: #f8fafc;
    font-weight: 600;
    color: #555;
}

.badge-status {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.72rem;
    font-weight: 600;
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
}

.modal.show {
    display: flex;
}

.modal-content {
    background: #ffffff;
    width: 100%;
    max-width: 480px;
    border-radius: 10px;
    padding: 20px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 12px;
}

.form-group label {
    display: block;
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 4px;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.85rem;
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
</style>

<div class="management-grid">
    <div class="card-box">
        <div class="card-header-flex">
            <h3><i class="fas fa-school"></i> Classes/Clubs</h3>
            <button class="btn-primary-sm" onclick="openModal('addClassModal')">
                <i class="fas fa-plus"></i> Add Class/Club
            </button>
        </div>

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

    <div class="card-box">
        <div class="card-header-flex">
            <h3><i class="fas fa-book"></i> Subjects</h3>
            <button class="btn-primary-sm" onclick="openModal('addSubjectModal')">
                <i class="fas fa-plus"></i> Assign Subject
            </button>
        </div>

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