package com.example.android.u_car_ibe;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.widget.Toast;

public class Pasajero extends AppCompatActivity {

    private Sesiones sesion;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_pasajero);
        sesion = new Sesiones(this);
        sesion.verificarConn(false);

        //Revisar si tiene coordenada guardada

        GuardarRuta();
        Toast.makeText(this, "Por favor ingresa un marcador", Toast.LENGTH_LONG ).show();
    }

    public void GuardarRuta(){
        Intent ruta= new Intent(this, GuardarCoordPass.class);
        startActivity(ruta);
    }
}
