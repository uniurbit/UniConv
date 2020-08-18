<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;
use PDF;
use App\User;
use App\Convenzione;
use JWTAuth;

//./vendor/bin/phpunit  --testsuite Feature --filter testGeneratePDF

class PDFTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    //./vendor/bin/phpunit  --testsuite Feature --filter testGeneratePDF
    public function testGeneratePDF()
    {
        $data = ['title' => 'PDF Test'];
        $pdf = PDF::loadView('myPDF', $data);

        Storage::disk('local')->delete('test.pdf');
        //$res = $pdf->save(base_path('tests\Feature\test.pdf',true));
        Storage::disk('local')->put('test.pdf', $pdf->output());      
        $exists = Storage::disk('local')->exists('test.pdf');        

        $this->assertTrue($exists);
    }


        /**
     * A basic test example.
     *
     * @return void
     */
    // public function testGenerateConvenzionePDF()
    // {
    //     $data = [
    //         'title' => 'Convenzione per attività conto terzi ',
    //         'descrizione_titolo' => 'titolo della convenzione',
    //         'dipartimento' => ['nome_breve'=>'nome dipartimento'],
    //         'nominativo_docente' => 'direttore dipartimento',
    //         'tipoemittente' => ['descrizione'=>'prova emittente']
    //     ];
        
    //     $pdf = PDF::loadView('convenzione', $data)
    //         ->setOption('encoding', 'utf-8')
    //         ->setOption('margin-left','20')
    //         ->setOption('margin-right','20')
    //         ->setOption('margin-top','30')
    //         ->setOption('margin-bottom','20');

    //     Storage::disk('local')->delete('convenzione.pdf');
    //     //$res = $pdf->save(base_path('tests\Feature\test.pdf',true));
    //     Storage::disk('local')->put('convenzione.pdf', $pdf->output());      
    //     $exists = Storage::disk('local')->exists('convenzione.pdf');        

    //     $this->assertTrue($exists);
    // }

    public function testPDFApigenerapdf()
    {
        $user = User::where('email','test.admin@uniurb.it')->first();
        $token = JWTAuth::fromUser( $user);
        $headers = ['Authorization' => "Bearer $token"];

        //memorizza ... una convenzione e con id genera pdf     
            
        $result = Convenzione::where('descrizione_titolo','like','convenzione di esempio')->first();

        $response = $this->json('GET', 'api/v1/convenzioni/generapdf/'.$result->id, [], $headers)
            ->assertStatus(200)
            ->assertHeader('Content-Type','application/pdf');
        //echo($response->getContent());

        //queste due chiamate servono per decondificare le external dipartimento e tipoemittente necessarie per la creazione 
        //della vista
        $result->dipartimento;
        $result->tipopagamento;        
        $result->azienda;
        $response = $this->json('POST', 'api/v1/convenzioni/generapdf',$result->toArray(), $headers)
            ->assertStatus(200)
            ->assertHeader('Content-Type','application/pdf');
    }

}
