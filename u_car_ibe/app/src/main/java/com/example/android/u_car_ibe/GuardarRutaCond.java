package com.example.android.u_car_ibe;

import android.Manifest;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.location.Location;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.ActivityCompat;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentActivity;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
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
import com.google.android.gms.maps.UiSettings;
import com.google.android.gms.maps.model.LatLng;
import com.google.android.gms.maps.model.MarkerOptions;
import com.google.android.gms.maps.model.Polyline;
import com.google.android.gms.maps.model.PolylineOptions;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

public class GuardarRutaCond extends FragmentActivity implements OnMapReadyCallback, GoogleApiClient.ConnectionCallbacks,
        GoogleApiClient.OnConnectionFailedListener,
        GoogleMap.OnMapLongClickListener,
        View.OnClickListener {

    private GoogleMap mMap;
    private GoogleApiClient mGoogleApiClient;
    LatLng[] coord;
    Location mLastLocation;
    private Sesiones sesion;
    LatLng Ucaribe= new LatLng(21.2013714,-86.8239155);
    private ArrayList<LatLng> arrayCoord = new ArrayList<LatLng>();


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        sesion = new Sesiones(this);
        sesion.verificarConn(false);
        setContentView(R.layout.activity_guardar_ruta_cond);
        // Obtain the SupportMapFragment and get notified when the map is ready to be used.
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
                Poly();
                Confirm();
                //String url= getMapsApiDirectionsUrl();
                //List<LatLng> poly= decodePoly(url);
            }
        });

    }


    /**
     * Manipulates the map once available.
     * This callback is triggered when the map is ready to be used.
     * This is where we can add markers or lines, add listeners or move the camera. In this case,
     * we just add a marker near Sydney, Australia.
     * If Google Play services is not installed on the device, the user will be prompted to install
     * it inside the SupportMapFragment. This method will only be triggered once the user has
     * installed Google Play services and returned to the app.
     */
    @Override
    public void onMapReady(GoogleMap googleMap) {
        mMap = googleMap;
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_FINE_LOCATION) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.ACCESS_COARSE_LOCATION) != PackageManager.PERMISSION_GRANTED) {
            // TODO: Consider calling
            //    ActivityCompat#requestPermissions
            // here to request the missing permissions, and then overriding
            //   public void onRequestPermissionsResult(int requestCode, String[] permissions,
            //                                          int[] grantResults)
            // to handle the case where the user grants the permission. See the documentation
            // for ActivityCompat#requestPermissions for more details.
            return;
        }
        mMap.setMyLocationEnabled(true);
        mMap.getUiSettings().setZoomControlsEnabled(false);
        // Add a marker in Sydney and move the camera
        //LatLng mylatlng = new LatLng(-34, 151);
        mMap.addMarker(new MarkerOptions().position(Ucaribe).title("Marker in Sydney"));
        //mMap.moveCamera(CameraUpdateFactory.newLatLng(mylatlng));
        //LatLng coordenadas= new LatLng(-23.684, 133.903);
        //mMap.moveCamera(CameraUpdateFactory.newLatLng(coordenadas));
        mMap.setOnMapLongClickListener(new GoogleMap.OnMapLongClickListener() {
            @Override
            public void onMapLongClick(LatLng latLng) {
                mMap.addMarker(new MarkerOptions().position(latLng).draggable(true));
                arrayCoord.add(latLng);

            }
        });
    }

    public void onLocationChanged(Location location) {
        mLastLocation = location;
    }

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

    public void moveMap(){
        LatLng mylatlng= new LatLng(mLastLocation.getLatitude(), mLastLocation.getLongitude());
        //mMap.addMarker(new MarkerOptions().position(mylatlng).draggable(true).title("actual"));
        mMap.moveCamera(CameraUpdateFactory.newLatLng(mylatlng));
        mMap.animateCamera(CameraUpdateFactory.zoomTo(15));
        mMap.getUiSettings().setZoomControlsEnabled(true);

    }


    public void onClick(View view) {
        Log.v("Algo","view click event");
    }

    public void onMapLongClick(LatLng latLng) {
        // mMap.clear();
        mMap.addMarker(new MarkerOptions().position(latLng).draggable(true));
    }

    @Override
    public void onConnectionSuspended(int i) {
        Toast.makeText(this, "Suspendida", Toast.LENGTH_LONG).show();
    }

    @Override
    protected void onStop() {
        super.onStop();
        //startService(new Intent(this, LocalizacionService.class));

        mGoogleApiClient.disconnect();
    }

    @Override
    public void onConnectionFailed(@NonNull ConnectionResult connectionResult) {
        Toast.makeText(this, "Error", Toast.LENGTH_LONG).show();
    }


    public void Poly(){
        if (arrayCoord.size() < 3){
            Toast.makeText(this, "Por favor, ingresa al menos 3 marcadores.", Toast.LENGTH_LONG);
            return;
        }

        //LatLng coordenadas= new LatLng(mLastLocation.getLatitude(), mLastLocation.getLongitude());
        arrayCoord.add(Ucaribe);
        coord= arrayCoord.toArray(new LatLng[arrayCoord.size()]);

        Polyline polyline1 = mMap.addPolyline(new PolylineOptions()
                .clickable(true)
                .add(
                        coord));
        //mMap.moveCamera(CameraUpdateFactory.newLatLng(coordenadas));
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
                        ActivConductor();


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

    public void ActivConductor(){
        Intent ruta= new Intent(this, Conductor.class);
        startActivity(ruta);
    }

    public void onBackPressed(){
        super.onBackPressed();
        Intent back = new Intent(this, LoginExitoso.class);
        startActivity(back);
        //finish();
    }



}

