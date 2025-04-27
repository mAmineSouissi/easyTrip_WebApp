import pandas as pd
import numpy as np
from sklearn.preprocessing import StandardScaler
from sklearn.cluster import KMeans
import joblib
import pymysql
import os

# Définir le chemin pour sauvegarder les modèles
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_DIR = os.path.join(BASE_DIR, '../../models')
os.makedirs(MODEL_DIR, exist_ok=True)

# Connexion à la base de données MySQL
try:
    conn = pymysql.connect(
        host='127.0.0.1',
        user='root',
        password='',
        database='easyTrip5',
        charset='utf8mb4',
        port=3306
    )
    print("Connexion à la base de données réussie")
except pymysql.MySQLError as e:
    print(f"Erreur de connexion à la base de données : {e}")
    exit(1)

# Requête SQL pour récupérer les données
query = """
SELECT h.id_hotel, h.name, h.city, h.rating, h.price, p.discount_percentage
FROM hotels h
LEFT JOIN promotion p ON h.promotion_id = p.id
"""
try:
    df = pd.read_sql(query, conn)
    print("Données récupérées avec succès")
except Exception as e:
    print(f"Erreur lors de l'exécution de la requête SQL : {e}")
    conn.close()
    exit(1)
conn.close()

# Vérifier si des données ont été récupérées
if df.empty:
    print("Aucune donnée récupérée. Vérifiez que la table 'hotels' contient des données.")
    exit(1)

# Préparation des données
df['discount_percentage'] = df['discount_percentage'].fillna(0)
df['city_encoded'] = df['city'].astype('category').cat.codes

# Sauvegarder le mapping des villes
city_mapping = dict(zip(df['city'], df['city_encoded']))
joblib.dump(city_mapping, os.path.join(MODEL_DIR, 'city_mapping.pkl'))

# Sélection des caractéristiques pour le clustering
features = ['rating', 'price', 'discount_percentage', 'city_encoded']
X = df[features]

# Normalisation des données
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# Entraînement du modèle K-Means
n_clusters = 5
kmeans = KMeans(n_clusters=n_clusters, random_state=42)
df['cluster'] = kmeans.fit_predict(X_scaled)

# Sauvegarde du modèle et du scaler
joblib.dump(kmeans, os.path.join(MODEL_DIR, 'kmeans_hotel_model.pkl'))
joblib.dump(scaler, os.path.join(MODEL_DIR, 'scaler.pkl'))

# Sauvegarde des données pour les recommandations
df[['id_hotel', 'name', 'cluster']].to_csv(os.path.join(MODEL_DIR, 'hotel_clusters.csv'), index=False)

print("Modèle entraîné et sauvegardé avec succès.")