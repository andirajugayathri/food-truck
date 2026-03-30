import os
from PIL import Image

def convert_to_webp(root_dir):
    extensions = ('.jpg', '.jpeg', '.png', '.gif')
    for subdir, dirs, files in os.walk(root_dir):
        for file in files:
            if file.lower().endswith(extensions):
                file_path = os.path.join(subdir, file)
                file_name, ext = os.path.splitext(file_path)
                output_path = f"{file_name}.webp"
                
                try:
                    with Image.open(file_path) as img:
                        # Convert to RGB if necessary (e.g. for PNGs with transparency if saving as JPEG, but WebP handles RGBA)
                        # WebP supports both lossy and lossless. We'll default to standard save.
                        img.save(output_path, 'webp')
                        print(f"Converted: {file_path} -> {output_path}")
                except Exception as e:
                    print(f"Failed to convert {file_path}: {e}")

if __name__ == "__main__":
    convert_to_webp('.')
