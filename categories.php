<?php
require_once __DIR__ . '/includes/header.php';

$userId = getUserId();

// Get all categories
$categories = fetchAll(
    "SELECT * FROM categories WHERE user_id = ? ORDER BY type, name",
    [$userId]
);

$incomeCategories = array_filter($categories, fn($c) => $c['type'] === 'income');
$expenseCategories = array_filter($categories, fn($c) => $c['type'] === 'expense');

$categoryIcons = ['briefcase', 'gift', 'trending-up', 'dollar-sign', 'coffee', 'car', 'shopping-cart', 'file-text', 'film', 'heart', 'book', 'home', 'utensils', 'plane', 'shopping-bag', 'gamepad'];
$categoryColors = ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#3B82F6', '#EC4899', '#14B8A6', '#F97316', '#06B6D4'];
?>

<div class="page-header mb-3 d-flex justify-between align-center">
    <div>
        <h1>Kategori</h1>
        <p style="color: var(--text-muted);">Kelola kategori pemasukan dan pengeluaran</p>
    </div>
    <button class="btn btn-primary" onclick="openAddCategoryModal()">
        <i class="fas fa-plus"></i>
        <span>Tambah Kategori</span>
    </button>
</div>

<div class="grid grid-2">
    <!-- Income Categories -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: var(--secondary);">
                <i class="fas fa-arrow-down"></i> Pemasukan
            </h3>
        </div>
        <div class="card-body">
            <?php if (empty($incomeCategories)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-tags"></i></div>
                    <p class="empty-state-description">Belum ada kategori pemasukan</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($incomeCategories as $cat): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.875rem; background: rgba(16, 185, 129, 0.05); border-radius: var(--radius-lg); border: 1px solid rgba(16, 185, 129, 0.2);">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: var(--radius-md); background: <?php echo $cat['color']; ?>20; color: <?php echo $cat['color']; ?>; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;"><?php echo $cat['name']; ?></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn btn-sm btn-outline" onclick='editCategory(<?php echo json_encode($cat); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Expense Categories -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title" style="color: var(--danger);">
                <i class="fas fa-arrow-up"></i> Pengeluaran
            </h3>
        </div>
        <div class="card-body">
            <?php if (empty($expenseCategories)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-tags"></i></div>
                    <p class="empty-state-description">Belum ada kategori pengeluaran</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php foreach ($expenseCategories as $cat): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.875rem; background: rgba(239, 68, 68, 0.05); border-radius: var(--radius-lg); border: 1px solid rgba(239, 68, 68, 0.2);">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: var(--radius-md); background: <?php echo $cat['color']; ?>20; color: <?php echo $cat['color']; ?>; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-<?php echo $cat['icon']; ?>"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 600;"><?php echo $cat['name']; ?></div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="btn btn-sm btn-outline" onclick='editCategory(<?php echo json_encode($cat); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal" id="categoryModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="categoryModalTitle">Tambah Kategori</h3>
            <button class="modal-close" onclick="closeModal('categoryModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="categoryForm" onsubmit="saveCategory(event)">
            <input type="hidden" id="category_id" name="id">
            
            <div class="form-group">
                <label for="category_name" class="form-label">Nama Kategori</label>
                <input type="text" id="category_name" name="name" class="form-control" placeholder="Contoh: Gaji, Makanan" required>
            </div>

            <div class="form-group">
                <label for="category_type" class="form-label">Tipe</label>
                <select id="category_type" name="type" class="form-control" required>
                    <option value="income">Pemasukan</option>
                    <option value="expense">Pengeluaran</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category_color" class="form-label">Warna</label>
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.5rem;" role="radiogroup" aria-label="Pilih warna kategori">
                    <?php foreach ($categoryColors as $color): ?>
                        <label style="cursor: pointer;">
                            <input type="radio" name="color" value="<?php echo $color; ?>" style="display: none;" class="color-radio-cat">
                            <div class="color-option-cat" style="width: 100%; height: 45px; background: <?php echo $color; ?>; border-radius: var(--radius-md); border: 2px solid transparent; transition: all var(--transition-fast);"></div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="category_icon" class="form-label">Icon</label>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem;" role="radiogroup" aria-label="Pilih icon kategori">
                    <?php foreach ($categoryIcons as $icon): ?>
                        <label style="cursor: pointer;">
                            <input type="radio" name="icon" value="<?php echo $icon; ?>" style="display: none;" class="icon-radio-cat">
                            <div class="icon-option-cat" style="width: 100%; height: 45px; background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-md); border: 2px solid transparent; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; transition: all var(--transition-fast);">
                                <i class="fas fa-<?php echo $icon; ?>"></i>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline" onclick="closeModal('categoryModal')" style="flex: 1;">Batal</button>
                <button type="submit" class="btn btn-primary" id="saveCategoryBtn" style="flex: 1;">
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddCategoryModal() {
    document.getElementById('categoryModalTitle').textContent = 'Tambah Kategori';
    document.getElementById('categoryForm').reset();
    document.getElementById('category_id').value = '';
    
    document.querySelector('input[name="color"][value="<?php echo $categoryColors[0]; ?>"]').checked = true;
    document.querySelector('input[name="icon"][value="briefcase"]').checked = true;
    updateCategorySelections();
    
    openModal('categoryModal');
}

function editCategory(category) {
    document.getElementById('categoryModalTitle').textContent = 'Edit Kategori';
    document.getElementById('category_id').value = category.id;
    document.getElementById('category_name').value = category.name;
    document.getElementById('category_type').value = category.type;
    
    document.querySelector(`input[name="color"][value="${category.color}"]`).checked = true;
    document.querySelector(`input[name="icon"][value="${category.icon}"]`).checked = true;
    updateCategorySelections();
    
    openModal('categoryModal');
}

async function saveCategory(event) {
    event.preventDefault();
    
    const btn = document.getElementById('saveCategoryBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div><span>Menyimpan...</span>';
    
    const formData = new FormData(event.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const response = await fetch('/uangnew/api/categories.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            showError(result.error || 'Terjadi kesalahan');
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan</span>';
        }
    } catch (error) {
        showError('Terjadi kesalahan: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<span>Simpan</span>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateCategorySelections();
    
    document.querySelectorAll('.color-radio-cat').forEach(radio => {
        radio.addEventListener('change', updateCategorySelections);
    });
    
    document.querySelectorAll('.icon-radio-cat').forEach(radio => {
        radio.addEventListener('change', updateCategorySelections);
    });
});

function updateCategorySelections() {
    document.querySelectorAll('.color-option-cat').forEach(el => {
        const radio = el.previousElementSibling || el.parentElement.querySelector('input');
        if (radio && radio.checked) {
            el.style.borderColor = 'white';
            el.style.transform = 'scale(1.1)';
        } else {
            el.style.borderColor = 'transparent';
            el.style.transform = 'scale(1)';
        }
    });
    
    document.querySelectorAll('.icon-option-cat').forEach(el => {
        const radio = el.previousElementSibling || el.parentElement.querySelector('input');
        if (radio && radio.checked) {
            el.style.borderColor = 'var(--primary)';
            el.style.background = 'rgba(99, 102, 241, 0.2)';
            el.style.transform = 'scale(1.1)';
        } else {
            el.style.borderColor = 'transparent';
            el.style.background = 'rgba(99, 102, 241, 0.1)';
            el.style.transform = 'scale(1)';
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
