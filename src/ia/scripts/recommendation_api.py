from flask import Flask, request, jsonify
import pandas as pd
import numpy as np
import joblib
import os

app = Flask(__name__)

# Définir les chemins des fichiers
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_DIR = os.path.join(BASE_DIR, '../../models')

# Charger le modèle et les données
try:
    kmeans = joblib.load(os.path.join(MODEL_DIR, 'kmeans_hotel_model.pkl'))
    scaler = joblib.load(os.path.join(MODEL_DIR, 'scaler.pkl'))
    hotel_clusters = pd.read_csv(os.path.join(MODEL_DIR, 'hotel_clusters.csv'))
    city_mapping = joblib.load(os.path.join(MODEL_DIR, 'city_mapping.pkl'))
except Exception as e:
    print(f"Erreur lors du chargement des fichiers de modèle : {e}")
    exit(1)

@app.route('/recommend', methods=['POST'])
def recommend_hotels():
    try:
        data = request.get_json()
        rating = float(data.get('rating', 3))
        max_price = float(data.get('max_price', 500))
        city = data.get('city', 'Paris')
        discount_preference = float(data.get('discount_preference', 0))

        # Encoder les préférences
        city_encoded = city_mapping.get(city, 0)
        user_features = np.array([[rating, max_price, discount_preference, city_encoded]])
        user_features_scaled = scaler.transform(user_features)

        # Prédire le cluster le plus proche
        cluster = kmeans.predict(user_features_scaled)[0]

        # Filtrer les hôtels du cluster correspondant
        recommended_hotels = hotel_clusters[hotel_clusters['cluster'] == cluster][['id_hotel', 'name']].to_dict('records')
        
        # Renommer 'id_hotel' en 'idHotel' pour correspondre à l'entité Hotels
        for hotel in recommended_hotels:
            hotel['idHotel'] = hotel.pop('id_hotel')

        return jsonify({
            'status': 'success',
            'recommendations': recommended_hotels
        })
    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)