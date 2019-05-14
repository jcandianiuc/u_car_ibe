package com.example.android.u_car_ibe;

import android.os.AsyncTask;
import android.os.Build;
import android.support.annotation.RequiresApi;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;

import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;

public class Espera extends AppCompatActivity {
    private Sesiones sesion;
    private String trip;
    private android.widget.TextView texto;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_espera);
        texto = (android.widget.TextView) findViewById(R.id.mensaje);
        sesion = new Sesiones(this);
        sesion.verificarConn(false);
        trip= sesion.obtenerTripId();
        trip= "trip_id="+ trip;
        String token= sesion.obtenerToken();
        SendJSON send= new SendJSON(trip, token);
        send.execute((Void) null);
    }

    public void cambiarMensaje(android.view.View v){
        java.text.SimpleDateFormat formato = new java.text.SimpleDateFormat("HH:mm:ss");
        java.util.Date fechaActual = java.util.Calendar.getInstance().getTime();

        String s = formato.format(fechaActual);
        texto.setText(String.format("Botón presionado: %s", s));
    }


    class SendJSON extends AsyncTask<Void, Void, Boolean> {
        private String jsonString;
        private String token;
        Sesiones sesion;


        SendJSON(String json, String tkn) {
            jsonString = json;
            token= tkn;
        }

        @RequiresApi(api = Build.VERSION_CODES.KITKAT)
        protected Boolean doInBackground(Void... voids) {
            try {

                Send(jsonString);
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }

        //protected void onPostExecute(Boolean success) {
        //}

        @RequiresApi(api = Build.VERSION_CODES.KITKAT)
        public void Send(String json) throws IOException {
            InputStream is;
            String result;


            try {
                URL url = new URL("http://187.153.58.129/trip/proposal");
                HttpURLConnection httpConn = (HttpURLConnection) url.openConnection();//Se realiza la conexión

                httpConn.setRequestMethod("GET");
                

                //httpConn.setRequestProperty("Content-Type", "application/json");
                //httpConn.setRequestProperty("Accept", "application/json");
                String tokenPrueba = "Token " + token;
                tokenPrueba = tokenPrueba.replace("\"", "");
                httpConn.setRequestProperty("Authorization", tokenPrueba);
                httpConn.setDoOutput(true);

                httpConn.connect();
                try (OutputStream os = httpConn.getOutputStream()) {
                    byte[] query = json.getBytes("utf-8");
                    os.write(query, 0, query.length);
                }
                int code = httpConn.getResponseCode();
                String resp= httpConn.getResponseMessage();


                is = httpConn.getInputStream(); //Se obtiene el resultado
                result = convertStreamToString(is);//Se convierte a String*/

                JSONObject jsonObject = new JSONObject(result);

                result = "popo";
            } catch (Exception e) {
                result = e.toString();
            }
        }


        private String convertStreamToString(InputStream is) throws IOException { //Para convertir a String
            if (is != null) {
                StringBuilder sb = new StringBuilder();
                String line;
                try {
                    BufferedReader reader = new BufferedReader(
                            new InputStreamReader(is, "UTF-8"));
                    while ((line = reader.readLine()) != null) {
                        sb.append(line);
                    }
                } finally {
                    is.close();
                }
                return sb.toString();
            } else {
                return "";
            }
        }

    }
}
