import requests
import xml.etree.ElementTree as ET
import pandas as pd
import os

# Calea fișierului XML descărcat
xml_file_path = '/home1/ki494232/public_html/public/uploads/feeds/b2b.xml'

# Calea pentru fișierul XLSX, aici salvăm fișierele convertite in XLSX

# xlsx_file_path = 'public/download/b2b_fișier-convertit_1.xlsx'
output_dir = '/home1/ki494232/public_html/public/download'

def get_next_filename(output_dir, base_name, extension):
    existing_files = [f for f in os.listdir(output_dir) if f.startswith(base_name) and f.endswith(extension)]
    existing_numbers = [int(f[len(base_name):-len(extension)]) for f in existing_files if f[len(base_name):-len(extension)].isdigit()]
    next_number = max(existing_numbers, default=0) + 1
    return f"{base_name}{next_number}{extension}"

def process_xml(xml_file_path):
    tree = ET.parse(xml_file_path)
    root = tree.getroot()

    products = []
    for product in root.findall('product'):
        product_data = {
            'name': product.find('name').text,
            'description': product.find('description').text,
            'category_id': product.find('category_id').text,
            'multi_categories': product.find('multi_categories').text,
            'brand_id': product.find('brand_id').text,
            'video_provider': product.find('video_provider').text,
            'video_link': product.find('video_link').text,
            'tags': product.find('tags').text,
            'unit_price': product.find('price').text,
            'unit': product.find('unit').text,
            'slug': product.find('slug').text,
            'current_stock': product.find('current_stock').text,
            'est_shipping_days': product.find('est_shipping_days').text,
            'sku': product.find('sku').text,
            'meta_title': product.find('meta_title').text,
            'meta_description': product.find('meta_description').text,
            'thumbnail_img': product.find('thumbnail_img').text,
            'photos': product.find('photos').text,
        }
        products.append(product_data)
    return products
    except Exception as e:
        print(f"Error processing XML: {e}")
        return []

def save_to_xlsx(products, xlsx_file_path):
    try:
        df = pd.DataFrame(products)
        df.to_excel(xlsx_file_path, index=False)
        print(f"XLSX file saved to {xlsx_file_path}")
    except Exception as e:
        print(f"Error saving XLSX: {e}")

# Generăm numele fișierului XLSX
xlsx_file_path = os.path.join(output_dir, get_next_filename(output_dir, 'b2b_fișier-convertit_', '.xlsx'))

# Executarea funcțiilor
products = process_xml(xml_file_path)
if products:
    save_to_xlsx(products, xlsx_file_path)
else:
    print("No products found or error in XML processing.")
