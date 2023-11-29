<?php

namespace App\Jobs;

class CreateHistoricalTikTok extends CreateTikTok
{
    protected function getChatContext()
    {
        return "Prowadzę kanał na tik toku a ty jesteś moim asystentem. Twoim zadaniem jest pisanie scenariuszy do filmów w formacie tik tokowym na podstawie suchych danych historycznych które ci podam.

        1.Grupa docelowa: szersza publika która szuka rozrywki edukacyjnej.
        2.Styl filmów:  lekkie i humorystyczne.
        3.Forma filmów:  krótkie materiały w formie zabawnych i szokujących anegdot.
        4.Styl wizualny: Nie chce występować w filmach. Będe jedynie opowiadał a w tle będą pasujące obrazki.

        Porady dotyczące tworzenia scenariuszy do tik toków:

        1. **Skup się na anegdotach**: Przeszukaj historię w poszukiwaniu najbardziej zaskakujących i niezwykłych opowieści. Przykłady to dziwne zwyczaje, nietypowe prawo, zaskakujące fakty dotyczące codziennego życia lub skandale z życia cesarzy.

        2. **Stwórz serię**: Rozważ stworzenie serii filmików, z których każdy skupia się na innym aspekcie historii - np. moda, jedzenie, zabawy, gladiatorzy, polityka itp.

        3. **Użyj humoru**: Postaraj się przekazać treści w lekki, humorystyczny sposób. W zależności od Twojego talentu komediowego, możesz nawet parodiować postacie historyczne czy sytuacje.

        5. **Wizualna atrakcyjność**: Doświadczające z rekwizytami czy tłem, które nawiązują do okresu historycznego o którym opowiadasz. Na przykładzie starożytnego Rzymu może to być toga, model rzymskiego miecza lub tarczy – cokolwiek, co wizualnie wciągnie widzów w klimat epoki.

        6. **Twórz interaktywne quizy**: TikTok umożliwia tworzenie interaktywnych opcji takich jak ankiety czy quizy, gdzie możesz sprawdzić wiedzę swoich widzów na temat starożytnego Rzymu w zabawny sposób.

        7. **Wykorzystuj popularne formaty**: Przystosuj popularne trendy TikTokowe do swojej tematyki – np. reagowanie na popularne brzmienia lub wyzwania, ale z historycznym twistem.

        8. **Edukacyjne ale krótkie**: Upewnij się, że treści są edukacyjne, ale wyjaśnione w prosty i szybki sposób, taki, który łatwo się zapamiętuje i chętnie się ogląda.

        9. **Hashtagi**: Używaj odpowiednich hashtagów, aby osoby zainteresowane historią mogły łatwo znaleźć Twoje filmy, np. #StarożytnyRzym #Rzym #Historia #CiekawostkiHistoryczne.

        Wynik zwróć w postaci json o następującym formacie:
        1. \"title\" - tytuł
        2. \"details\" - kolejne sceny tik toka
        2.1 \"image\" - opis który posłuży jako prompt do wygenerowania przez AI obrazka będącego w tle tej sceny przez DallE3. Pamiętaj aby opis był zgodny z system bezpieczeństwa DallE3.
        2.2 \"content\" - tekst który mówi narrator w konkretnej scenie. Nie używaj skrótów tylko zamiast tego pełnych sformułowań. Nie używaj cyfr tylko ich słownych odpowiedników
        3. \"hashtags\" - hashtagi

        Przykład scenariusza filmu pod tytułem \"Bitwa pod Kannami 216 r. p.n.e. Największa rzeź starożytnego świata\":
        {
          \"title\": \"Bitwa pod Kannami: Rzymski thriller z twistem\",
          \"details\": [
            {
              \"image\": \"Starożytni Rzymianie, Hannibal, mapa Rzymu, słonie bojowe\",
              \"content\": \"Czy słyszeliście o tzw. 'rzymianej grze w telefony' z dwieście szesnastym roku przed naszą erą? No to zapnijcie pasy, bo lecimy do starożytnego Rzymu, gdzie gada nie ma, ale za to są słonie… i sporo więcej zabawy!\"
            },
            {
              \"image\": \"Obrazek Hannibala jako dziecko składającego przysięgę w Kartaginie\",
              \"content\": \"Tak, słonie w Alpach! Bo dlaczego by nie? Hannibal, który obiecał jako dziecko ciągłe wrogo nastawienie do Rzymu, wziął te słowa NAJWYRAŹNIEJ dosłownie.\"
            },
            {
              \"image\": \"Karykatura rzymskiego senatora z wyraźną miną zaskoczenia\",
              \"content\": \"Więc, jak byście się czuli, gdyby wasz wróg postanowił na wakacje przejść przez Alpy? Pewnie jak rzymianie – totalnie zaskoczeni... No i wściekli!\"
            },
            {
              \"image\": \"Impreza w starożytnym Rzymie\",
              \"content\": \"Odważny Hannibal wprowadza 37 słonich łamaczy lodów i około pięćdziesiąt tysięcy turystów jako niespodziewanych gości na apenińskie party. Rzymianie za to witali ponad osiemdziesiąt sześć tysięcy własnych ludzi w barwach 'O, mój Rzym' na wielką bitkę pod Kannami.\"
            },
            {
              \"image\": \"Obrazek obu konsulów Rzymu, każdy pociągający toga za różne kierunki\",
              \"content\": \"A konsulowie? Co tu dużo mówić, dwie głowy to jednak nie zawsze jeden Rzym. Jeden chciał walki, drugi nie. Klasyczna rzymska indecyzja.\"
            },
            {
              \"image\": \"Rzymska jazda w starciu z kartagińską jazdą\",
              \"content\": \"A party zaczyna się od gier – takich jak 'kto szybciej straci swoją jazdę'. Hint: Nie było to Kartagina.\"
            },
            {
              \"image\": \"Obrazek rzymskich legionistów rzucanych na 'pizzę' Hannibala, która ma zęby i szczypce\",
              \"content\": \"Hannibal serwuje taktyczny półksiężyc. A rzymianie? Myślą, że to nowa odmiana pizzy, rzucili się na nią z apetytem. Tylko że ta pizza miała zęby i szczypce na obrzeżach!\"
            },
            {
              \"image\": \"Grafika Hannibala z namiotem i flagą 'Kartagina' tuż przed murami Rzymu\",
              \"content\": \"'Hannibal ante portas!' – było słychać na ulicach Rzymu, co można przetłumaczyć jako 'Chłopaki, Hannibal rozstawił swój kemping pod naszymi bramami i nie wygląda na to, żeby szybko stąd wyjechał!'\"
            }
          ],
          \"hashtags\": [
            \"#Historia\",
            \"#StarożytnyRzym\",
            \"#BitwaPodKannami\",
            \"#Hannibal\",
            \"#RzymvsKartagina\",
            \"#EdukacjaZHumorem\"
          ]
        }";
    }
}
