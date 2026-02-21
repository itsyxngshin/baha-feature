import sys
import json
import pandas as pd
import joblib
import warnings

# Suppress sklearn warnings to keep JSON output clean
warnings.filterwarnings("ignore")

def main():
    try:
        # 1. Check Arguments
        if len(sys.argv) < 3:
            raise ValueError(f"Required 2 arguments, got {len(sys.argv)-1}")

        model_path = sys.argv[1] 
        input_json = sys.argv[2].strip() # Clean potential whitespace
        
        # 2. Load Model
        model = joblib.load(model_path)
        
        # 3. Parse JSON
        try:
            data = json.loads(input_json)
        except json.JSONDecodeError as je:
            raise ValueError(f"Invalid JSON received: {str(je)}. Received: {input_json}")
        
        # 4. Create DataFrame (Ensure keys match your TURO-MOKO training data exactly)
        features = {
            'Rainfall_mm_hr': [float(data.get('rainfall', 0))],
            'Previous_Rainfall_mm': [float(data.get('prev_rainfall', 0))],
            'Elevation_m': [float(data.get('elevation', 0))],
            'Drainage_Level': [float(data.get('drainage', 0))]
        }
        df = pd.DataFrame(features)
        
        # 5. Predict
        prediction = model.predict(df)[0]
        
        # 6. Return JSON to Laravel
        # Use max(0, ...) to ensure no negative flood heights
        print(json.dumps({
            "status": "success",
            "water_level": round(max(0, float(prediction)), 2)
        }))

    except Exception as e:
        # Always return valid JSON even on error
        print(json.dumps({
            "status": "error", 
            "message": str(e)
        }))

if __name__ == "__main__":
    main()