
describe('verifica regexp', () => {

it('test verifica', () => {

    let text = `Delibera n. 217/2017/DiSPeA                
    Estratto del VERBALE n.16/2017
    CONSIGLIO DEL DIPARTIMENTO DI SCIENZE PURE E APPLICATE (DiSPeA)
    Riunione del giorno 06/12/2017
    Il giorno 6 dicembre 2017, in seguito a convocazione ordinaria prot. n. 34728 del 30 novembre 2017
    inviata per mezzo e-mail, il Consiglio del Dipartimento di Scienze Pure e Applicate (DiSPeA) si è
    riunito alle ore 11,00 presso l’Aula B1 di Palazzo Albani (Via del Balestriere – Urbino) per discutere e'
    deliberare sul seguente O.d.G.:
    …………………………….OMISSIS……………………
    4. Accordi, contratti, convenzioni, contributi per attività di ricerca e didattica e attività conto`
    
    let number = text.match(/[d|D]elibera n.?\s?([A-Za-z0-9\/]*)\s*\n/);
    
    console.log(number[1]);
    expect(number[1]).toBe('217/2017/DiSPeA');    
});



});