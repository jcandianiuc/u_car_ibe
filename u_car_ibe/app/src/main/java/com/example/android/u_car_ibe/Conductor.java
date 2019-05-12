package com.example.android.u_car_ibe;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.widget.Toast;

public class Conductor extends AppCompatActivity {
    private Sesiones sesion;

    @SuppressLint("ShowToast")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_conductor);
        sesion = new Sesiones(this);
        sesion.verificarConn(false);

        //Checar si tiene ruta guardada
        /*if (sesion.obtenerConfirmRuta() == false) {
            GuardarRuta();
            Toast.makeText(this, "Por favor ingresa tu ruta a través de marcadores", Toast.LENGTH_LONG ).show();
        }*/



    }

    public void GuardarRuta(){
        Intent ruta= new Intent(this, GuardarRutaCond.class);
        startActivity(ruta);
    }
}
