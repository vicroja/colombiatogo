import tkinter as tk
from tkinter import filedialog, messagebox
import os
import sys
import subprocess
from datetime import datetime

class FileMergerApp:
    def __init__(self, root):
        self.root = root
        self.root.title("Fusionador de Archivos")
        self.root.geometry("700x650")

        self.folder_list = []
        self.file_list = []

        # --- Frame para la selección ---
        self.selection_frame = tk.Frame(self.root, pady=5)
        self.selection_frame.pack(fill="x", padx=10)

        self.btn_add_folder = tk.Button(self.selection_frame, text="Agregar Carpetas", command=self.add_folders)
        self.btn_add_folder.pack(side="left", padx=(0, 5))

        self.btn_add_file = tk.Button(self.selection_frame, text="Agregar Archivos", command=self.add_files)
        self.btn_add_file.pack(side="left", padx=5)

        self.btn_clear_all = tk.Button(self.selection_frame, text="Limpiar Todo", command=self.clear_all_lists)
        self.btn_clear_all.pack(side="right", padx=5)

        # --- Frame para las listas y sus botones ---
        self.lists_frame = tk.Frame(self.root, padx=10, pady=5)
        self.lists_frame.pack(fill="both", expand=True)
        self.lists_frame.columnconfigure(0, weight=1)
        self.lists_frame.rowconfigure(1, weight=1)
        self.lists_frame.rowconfigure(4, weight=1)

        # --- Lista de Carpetas ---
        tk.Label(self.lists_frame, text="Carpetas Seleccionadas:", anchor="w").grid(row=0, column=0, sticky="ew", pady=(5,0))

        self.folder_list_frame = tk.Frame(self.lists_frame)
        self.folder_list_frame.grid(row=1, column=0, sticky="nsew")
        self.folder_scrollbar = tk.Scrollbar(self.folder_list_frame)
        self.folder_scrollbar.pack(side="right", fill="y")
        self.folder_listbox = tk.Listbox(self.folder_list_frame, selectmode=tk.SINGLE, yscrollcommand=self.folder_scrollbar.set)
        self.folder_listbox.pack(side="left", fill="both", expand=True)
        self.folder_scrollbar.config(command=self.folder_listbox.yview)

        self.btn_remove_folder = tk.Button(self.lists_frame, text="Quitar Carpeta", command=self.remove_folder)
        self.btn_remove_folder.grid(row=2, column=0, sticky="w", pady=(2,10))

        # --- Lista de Archivos ---
        tk.Label(self.lists_frame, text="Archivos Individuales Seleccionados:", anchor="w").grid(row=3, column=0, sticky="ew")

        self.file_list_frame = tk.Frame(self.lists_frame)
        self.file_list_frame.grid(row=4, column=0, sticky="nsew")
        self.file_scrollbar = tk.Scrollbar(self.file_list_frame)
        self.file_scrollbar.pack(side="right", fill="y")
        self.file_listbox = tk.Listbox(self.file_list_frame, selectmode=tk.SINGLE, yscrollcommand=self.file_scrollbar.set)
        self.file_listbox.pack(side="left", fill="both", expand=True)
        self.file_scrollbar.config(command=self.file_listbox.yview)

        self.btn_remove_file = tk.Button(self.lists_frame, text="Quitar Archivo", command=self.remove_file)
        self.btn_remove_file.grid(row=5, column=0, sticky="w", pady=(2,5))

        # --- Frame para el filtro y el botón de fusión ---
        self.frame_bottom = tk.Frame(self.root, pady=10)
        self.frame_bottom.pack(fill="x", padx=10)

        tk.Label(self.frame_bottom, text="Filtrar por nombre de archivo (opcional):").pack(pady=(0, 2))
        self.filter_entry = tk.Entry(self.frame_bottom)
        self.filter_entry.pack(fill="x", pady=(0, 10))

        self.btn_merge = tk.Button(self.frame_bottom, text="Unir Archivos en un .txt", font=("Helvetica", 10, "bold"), command=self.merge_files)
        self.btn_merge.pack()

    def add_folders(self):
        folder = filedialog.askdirectory(mustexist=True, title="Selecciona una carpeta")
        if folder and folder not in self.folder_list:
            self.folder_list.append(folder)
            self.update_folder_listbox()

    def add_files(self):
        files = filedialog.askopenfilenames(
            title="Selecciona archivos",
            filetypes=[("Archivos Soportados", "*.php *.js *.css"), ("Todos los archivos", "*.*")]
        )
        for file in files:
            if file not in self.file_list:
                self.file_list.append(file)
        self.update_file_listbox()

    def clear_all_lists(self):
        self.folder_list.clear()
        self.file_list.clear()
        self.update_folder_listbox()
        self.update_file_listbox()

    def remove_folder(self):
        selected_indices = self.folder_listbox.curselection()
        if not selected_indices: return
        selected_item = self.folder_listbox.get(selected_indices[0])
        if selected_item in self.folder_list:
            self.folder_list.remove(selected_item)
            self.update_folder_listbox()

    def remove_file(self):
        selected_indices = self.file_listbox.curselection()
        if not selected_indices: return
        selected_item = self.file_listbox.get(selected_indices[0])
        if selected_item in self.file_list:
            self.file_list.remove(selected_item)
            self.update_file_listbox()

    def update_folder_listbox(self):
        self.folder_listbox.delete(0, tk.END)
        for item in self.folder_list:
            self.folder_listbox.insert(tk.END, item)

    def update_file_listbox(self):
        self.file_listbox.delete(0, tk.END)
        for item in self.file_list:
            self.file_listbox.insert(tk.END, item)

    def merge_files(self):
        if not self.folder_list and not self.file_list:
            messagebox.showerror("Error", "No has seleccionado ninguna carpeta o archivo.")
            return

        # --- MODIFICADO: Generar nombre de archivo y usarlo directamente ---
        # Se define la ruta del script para guardar el archivo en el mismo lugar.
        script_dir = os.path.dirname(os.path.abspath(__file__)) if '__file__' in locals() else os.getcwd()
        now = datetime.now()
        timestamp = now.strftime("%Y-%m-%d_%H-%M")
        default_filename = f"unidos_{timestamp}.txt"

        # Esta es la ruta final del archivo de salida.
        output_filename = os.path.join(script_dir, default_filename)

        # Se elimina la ventana de "Guardar como...".
        # if not output_filename:
        #     return

        filter_text = self.filter_entry.get().lower().strip()
        processed_files = set()

        try:
            with open(output_filename, 'w', encoding='utf-8') as outfile:

                def write_file_content(file_path):
                    if file_path in processed_files:
                        return
                    filename = os.path.basename(file_path)

                    if filter_text and filter_text not in filename.lower():
                        return

                    header = f"--- INICIO: {filename} | RUTA: {file_path} ---\n\n"
                    outfile.write(header)
                    try:
                        with open(file_path, 'r', encoding='utf-8', errors='ignore') as infile:
                            outfile.write(infile.read())
                        outfile.write(f"\n\n--- FIN: {filename} ---\n\n")
                    except Exception as e:
                        outfile.write(f"Error al leer el archivo: {e}\n\n")
                        outfile.write(f"--- FIN CON ERROR: {filename} ---\n\n")
                    processed_files.add(file_path)

                # Procesar archivos de carpetas
                for folder_path in self.folder_list:
                    for root_dir, _, files in os.walk(folder_path):
                        for filename in files:
                            if filename.endswith((".php", ".js", ".css")):
                                file_path = os.path.join(root_dir, filename)
                                write_file_content(file_path)

                # Procesar archivos individuales
                for file_path in self.file_list:
                    write_file_content(file_path)

            self.open_file_location(output_filename)

        except Exception as e:
            print(f"Ocurrió un error al escribir el archivo de salida:\n{e}")

    def open_file_location(self, file_path):
        try:
            if sys.platform == "win32":
                subprocess.run(['explorer', '/select,', os.path.normpath(file_path)])
            elif sys.platform == "darwin": # macOS
                subprocess.run(['open', '-R', file_path])
            else: # linux
                subprocess.run(['xdg-open', os.path.dirname(file_path)])
        except Exception as e:
            print(f"No se pudo abrir la ubicación del archivo. Error: {e}")

if __name__ == "__main__":
    root = tk.Tk()
    app = FileMergerApp(root)
    root.mainloop()