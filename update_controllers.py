import os
import glob
import re

def update_controller(name_singular, name_plural):
    path = f"app/Http/Controllers/{name_singular}Controller.php"
    with open(path, 'r') as f:
        content = f.read()

    # Update index method
    index_method = f"""    public function index(Request $request)
    {{
        $query = {name_singular}::query();
        if ($request->filled('search')) {{
            $query->where('name', 'like', '%' . $request->search . '%');
        }}
        ${name_plural} = $query->orderBy('name')->paginate(10);

        if ($request->header('HX-Target') === '{name_plural}-list') {{
            return view('{name_plural}.{name_plural}-list', compact('{name_plural}'));
        }}
        return view('{name_plural}.{name_plural}-index', compact('{name_plural}'));
    }}"""
    
    content = re.sub(r'public function index.*?\{.*?(?=\n    public function create)', index_method + "\n", content, flags=re.DOTALL)
    
    # Replace view names from entity.view to entity.entity-view
    content = content.replace(f"view('{name_plural}.form", f"view('{name_plural}.{name_plural}-form")
    content = content.replace(f"view('{name_plural}.list", f"view('{name_plural}.{name_plural}-list")
    
    # Replace get() with paginate(10) in store, update, destroy
    content = content.replace("->get()])", "->paginate(10)])")
    
    with open(path, 'w') as f:
        f.write(content)


for entity in [('Material', 'materials'), ('Product', 'products'), ('Contact', 'contacts')]:
    update_controller(entity[0], entity[1])

print("Controllers updated.")
