import sys
import joblib
import json
import pandas as pd
import numpy as np
import warnings

warnings.filterwarnings('ignore')

# Cargar el CSV
df = pd.read_csv("../storage/app/private/Productos_finales(1.2).csv", sep=",", low_memory=False)

# Definir funciones explícitas para selección de datos
def select_text_column(x):
    return x.iloc[:, 0]

def select_num_columns(x):
    return x.iloc[:, 1:]

# Cargar el modelo y las funciones personalizadas
model_path = "../storage/app/private/modelo_productos_similares.pkl"
data = joblib.load(model_path)
pipeline = data['pipeline']
X_transformed = data['X_transformed']

# Definir la función para encontrar los productos más cercanos
def find_nearest_products(input_index, k=6):
    if input_index < 0 or input_index >= len(df):
        return {"error": "Invalid product index"}

    input_super = df.iloc[input_index]['supermercado']
    
    # Obtener los índices de los k+1 vecinos más cercanos (incluyendo el producto de entrada)
    distances, indices = pipeline.named_steps['nn'].kneighbors(X_transformed[input_index:input_index+1], n_neighbors=X_transformed.shape[0])

    # Filtrar los productos del mismo supermercado
    same_super_indices = [idx for idx in indices[0] if df.iloc[idx]['supermercado'] == input_super]
    
    # Seleccionar los k productos más cercanos (excluyendo el producto de entrada si está en la lista)
    nearest_indices = [idx for idx in same_super_indices if idx != input_index][:k]
    
    # Obtener los productos correspondientes
    nearest_products = df.iloc[nearest_indices]

    return nearest_products.to_dict(orient="records")  # Convert to JSON serializable format

# Obtener el ID del producto desde PHP
product_id = int(sys.argv[1])

# Encontrar productos similares
similar_products = find_nearest_products(product_id)

# Devolver los resultados en formato JSON
print(json.dumps(similar_products))

