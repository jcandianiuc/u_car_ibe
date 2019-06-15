package com.example.android.u_car_ibe;

import android.Manifest;
import android.app.Activity;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.location.Location;
import android.os.AsyncTask;
import android.os.Build;
import android.support.annotation.LayoutRes;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.annotation.RequiresApi;
import android.support.v4.app.ActivityCompat;
import android.support.v4.app.FragmentActivity;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import com.google.android.gms.common.ConnectionResult;
import com.google.android.gms.common.api.GoogleApiClient;
import com.google.android.gms.location.LocationRequest;
import com.google.android.gms.location.LocationServices;
import com.google.android.gms.maps.CameraUpdateFactory;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.net.HttpURLConnection;
import java.net.URL;

public class GuardarCoordPass extends FragmentActivity implements OnMapReadyCallback, GoogleApiClient.ConnectionCallbacks,
        GoogleApiClient.OnConnectionFailedListener,
        GoogleMap.OnMapLongClickListener,
        View.OnClickListener {

    private GoogleMap mMap;
    private GoogleApiClient mGoogleApiClient;
    Location mLastLocation;
    private Sesiones sesion;
    LatLng coordenada;
    String coordJSON;
    String jsonFinal;
    String prop;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        sesion = new Sesiones(this);
        sesion.verificarConn(false);
        setContentView(R.layout.activity_guardar_coord_pass);
        SupportMapFragment mapFragment = (SupportMapFragment) getSupportFragmentManager()
                .findFragmentById(R.id.map);
        mapFragment.getMapAsync(this);


        mGoogleApiClient = new GoogleApiClient.Builder(this) //Iniciar la api de Google
                .addConnectionCallbacks(this)
                .addOnConnectionFailedListener(this)
                .addApi(LocationServices.API)
                .enableAutoManage(this, this)
                .build();

        final Button confirm = (Button) findViewById(R.id.confirmar);
        confirm.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Confirm();
            }
        });

    }

    @Override
    public void onClick(View v) {

    }

    @Override
    public void onConnected(@Nullable Bundle bundle) {
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION)
                != PackageManager.PERMISSION_GRANTED) { //Permisos
            if (ActivityCompat.shouldShowRequestPermissionRationale(this,
                    Manifest.permission.ACCESS_FINE_LOCATION)) {
            } else {
                ActivityCompat.requestPermissions(this,
                        new String[]{Manifest.permission.ACCESS_FINE_LOCATION}, 2);

            }
        } else {
            mLastLocation = LocationServices.FusedLocationApi.getLastLocation(mGoogleApiClient);
            moveMap();

        }

    }

    public void moveMap() {
        LatLng mylatlng = new LatLng(mLastLocation.getLatitude(), mLastLocation.getLongitude());
        //mMap.addMarker(new MarkerOptions().position(mylatlng).draggable(true).title("actual"));
        mMap.moveCamera(CameraUpdateFactory.newLatLng(mylatlng));
        mMap.animateCamera(CameraUpdateFactory.zoomTo(15));
        mMap.getUiSettings().setZoomControlsEnabled(true);

    }

    @Override
    public void onConnectionSuspended(int i) {

    }

    @Override
    public void onConnectionFailed(@NonNull ConnectionResult connectionResult) {
        mGoogleApiClient.disconnect();
        Toast.makeText(this, "Error", Toast.LENGTH_LONG);
    }

    @Override
    public void onMapLongClick(LatLng latLng) {

    }

    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap;
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            return;
        }
        mMap.setMyLocationEnabled(true);
        mMap.getUiSettings().setZoomControlsEnabled(false);
        mMap.setOnMapLongClickListener(new GoogleMap.OnMapLongClickListener() {
            @Override
            public void onMapLongClick(LatLng latLng) {
                mMap.clear();
                mMap.addMarker(new MarkerOptions().position(latLng).draggable(true));
                coordenada= latLng;
            }
        });
    }

    public void Confirm(){

        AlertDialog.Builder builder = new AlertDialog.Builder(this);
        builder.setCancelable(true);
        builder.setTitle("Confirmar ruta");
        builder.setMessage("¿Estás seguro?");
        builder.setPositiveButton("Confirmar",
                new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        sesion.ConfirmarRuta(true);
                        try {
                            coordJSON= Coord2Json(coordenada);

                        } catch (JSONException e) {
                            e.printStackTrace();
                        }

                        try {
                            jsonFinal = CrearJSON(coordJSON);
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }

                        jsonFinal= jsonFinal.replace("\\", "");
                        jsonFinal = jsonFinal.replace("\"" + coordJSON + "\"", coordJSON);
                        SendJSON json= new SendJSON(jsonFinal, sesion.obtenerToken(), sesion);
                        json.execute((Void) null);

                        //ActivPasajero();
                        //sesion.guardarTrip(json.getTrip());


                    }
                });
        builder.setNegativeButton(android.R.string.cancel, new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
            }
        });

        AlertDialog dialog = builder.create();
        dialog.show();
    }

    public void ActivPasajero(){
        Intent ruta= new Intent(this, Espera.class);

        Intent intent = new Intent(GuardarCoordPass.this, Espera.class);
        intent.putExtra("proposal",prop);
        startActivity(intent);
    }

    public String Coord2Json(LatLng coord) throws JSONException {
        JSONObject json= new JSONObject();
        json.put("latitude", coord.latitude);
        json.put("longitude", coord.longitude);
        return "["+json.toString()+"]";
    }

    public String CrearJSON(String marker) throws JSONException {
        JSONObject json = new JSONObject();
        Boolean toUni = sesion.obtenerToUni();
        String dtime = sesion.obtenerDateTime();
        String role = "passenger";
        json.put("datetime", dtime);
        json.put("role", role);
        json.put("to_uni", toUni);
        json.put("markers", marker);

        return json.toString();
    }

    class SendJSON extends AsyncTask<Void, Void, Boolean> {
        private String jsonString;
        private String token;
        String tripID;
        Sesiones sesion;


        SendJSON(String json, String tkn, Sesiones sesion1) {
            jsonString = json;
            token = tkn;
            sesion = sesion1;
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

        protected void onPostExecute(Boolean success) {
            ActivPasajero();
        }

        @RequiresApi(api = Build.VERSION_CODES.KITKAT)
        public void Send(String json) throws IOException {
            InputStream is;
            String result;


            try {
                URL url = new URL("http://187.153.58.129/trip/start");
                HttpURLConnection httpConn = (HttpURLConnection) url.openConnection();//Se realiza la conexión

                httpConn.setRequestMethod("POST");
                httpConn.setRequestProperty("Content-Type", "application/json");
                httpConn.setRequestProperty("Accept", "application/json");
                String tokenPrueba = "Token " + token;
                tokenPrueba = tokenPrueba.replace("\"", "");
                httpConn.setRequestProperty("Authorization", tokenPrueba);
                httpConn.setDoOutput(true);

                //String input= "{\"id\":\"" + mUsername +"\"," + "\"password\":\"" + mPassword + "\"}";

                httpConn.connect();
                try (OutputStream os = httpConn.getOutputStream()) {
                    byte[] query = json.getBytes("utf-8");
                    os.write(query, 0, query.length);
                }
                int code = httpConn.getResponseCode();
                int algo = 0;


                is = httpConn.getInputStream(); //Se obtiene el resultado
                result = convertStreamToString(is);//Se convierte a String*/

                JSONObject jsonObject = new JSONObject(result);
                tripID = jsonObject.getString("trip_id");
                prop = jsonObject.getJSONObject("proposal").toString();

            /*String proposal= jsonObject.getString("proposal");

            if (proposal != "null"){
                //JSONObject = jsonObject.getJSONObject("proposal");
            }*/
                result = "popo";
                sesion.guardarTrip(tripID);
            } catch (Exception e) {
                result = e.toString();
            }
        }

    /*public String getTrip(){

        return tripID;
    }*/

        private String convertStreamToString(InputStream is) throws IOException { //Para convertir a String
            if (is != null) {
                StringBuilder sb = new StringBuilder();
                String line;
                try {
                    BufferedReader reader = new BufferedReader(
                            new InputStreamReader(is, "UTF-8"));
                    while ((line = reader.readLine()) != null) {
                        //sb.append(line).append("");
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

