-- SEED.SQL

-- Ovaj fajl sadrži default "demo" podatke.

-- Napomena za developere: Dodajte podatke u ovaj fajl ako:
--  - vaš kod ne radi ispravno bez barem nekih početnih podataka,
--  - nije moguće dodati nove podatke ako nema postojećih,
--  - su vaše tabele "šifrarnici" koje korisnik ne može direktno
-- unositi (osim ako je admin),
--  - postojanje default podataka drastično olakšava rad sa
-- vašim kodom.


SET names 'utf8';

--
-- Dumping data for table `akademska_godina`
-- U bazi mora postojati tačno jedna ak. g. označena kao "aktuelna"
--

INSERT INTO `akademska_godina` (`id`, `naziv`, `aktuelna`, `pocetak_zimskog_semestra`, `kraj_zimskog_semestra`, `pocetak_ljetnjeg_semestra`, `kraj_ljetnjeg_semestra`) VALUES
(1, '2015/2016', 1, '2015-10-05', '2016-01-17', '2016-02-22', '2016-06-06');


--
-- Dumping data for table `angazman_status`
-- Šifrarnik
--

INSERT INTO `angazman_status` (`id`, `naziv`) VALUES
(1, 'odgovorni nastavnik'),
(2, 'asistent'),
(3, 'demonstrator'),
(4, 'predavač - istaknuti stručnjak iz prakse'),
(5, 'asistent - istaknuti stručnjak iz prakse'),
(6, 'profesor emeritus');


--
-- Dumping data for table `anketa_tip_pitanja`
-- Šifrarnik
--

INSERT INTO `anketa_tip_pitanja` (`id`, `tip`, `postoji_izbor`, `tabela_odgovora`) VALUES
(1, 'Ocjena (skala 1..5)', 'Y', 'odgovor_rank'),
(2, 'Komentar', 'N', 'odgovor_text'),
(3, 'Izbor (pojedinačni)', 'Y', 'odgovor_izbor'),
(4, 'Izbor (višestruki)', 'Y', 'odgovor_izbor'),
(5, 'Naslov', 'N', ''),
(6, 'Podnaslov', 'N', '');


--
-- Dumping data for table `osoba`
-- Potreban je barem jedan admin korisnik kako bi bilo moguće
-- unijeti podatke i dodati ostale korisnike.
-- Vidjeti i tabelu auth.
--

INSERT INTO `osoba` (`id`, `ime`, `prezime`) VALUES
(1, 'Site', 'Admin');


--
-- Dumping data for table `auth`
-- Potreban je barem jedan admin korisnik kako bi bilo moguće
-- unijeti podatke i dodati ostale korisnike.
-- Vidjeti i tabelu osoba
-- PAŽNJA: Prije nego što omogućite javni pristup sistemu
-- promijenite password!
--

INSERT INTO `auth` (`id`, `login`, `password`, `admin`, `external_id`, `aktivan`) VALUES
(1, 'admin', 'admin', 0, '', 1);


--
-- Dumping data for table `drzava`
-- Šifrarnik
-- Ovdje očigledno nisu sve države svijeta pa bi trebalo
-- dopuniti šifrarnik.
--

INSERT INTO `drzava` (`id`, `naziv`) VALUES
(1, 'Bosna i Hercegovina'),
(2, 'Srbija'),
(3, 'Hrvatska'),
(4, 'Crna Gora'),
(5, 'Slovenija'),
(6, 'Kosovo'),
(7, 'Turska'),
(8, 'Njemačka'),
(9, 'Makedonija'),
(10, 'Iran'),
(11, 'Libija'),
(12, 'Švedska'),
(13, 'Austrija'),
(14, 'SAD'),
(15, 'Italija'),
(16, 'Australija'),
(17, 'Velika Britanija'),
(18, 'Malezija'),
(19, 'Holandija'),
(20, 'Švicarska'),
(21, 'Tajland'),
(22, 'Češka'),
(23, 'Slovačka'),
(24, 'Norveška'),
(25, 'Južna Koreja'),
(26, 'Jordan'),
(27, 'Francuska'),
(28, 'Egipat'),
(29, 'Rusija'),
(30, 'Irak'),
(31, 'Kuvajt');


--
-- Dumping data for table `ekstenzije`
-- Šifrarnik (ekstenzije datoteka za upload)
--

INSERT INTO `ekstenzije` (`id`, `naziv`) VALUES
(1, '.zip'),
(2, '.doc'),
(3, '.pdf'),
(4, '.odt'),
(5, '.docx'),
(6, '.txt'),
(7, '.rtf'),
(8, '.7z'),
(9, '.rar'),
(10, '.c'),
(11, '.cpp'),
(12, '.m'),
(13, '.fig'),
(14, '.jar'),
(15, '.java'),
(16, '.gz'),
(17, '.html'),
(18, '.php');


--
-- Dumping data for table `institucija`
-- Na nekoliko mjesta se očekuje da u bazi postoji barem 
-- jedna institucija
--

INSERT INTO `institucija` (`id`, `naziv`, `roditelj`, `kratki_naziv`, `tipinstitucije`, `dekan`, `broj_protokola`) VALUES
(1, 'Elektrotehnički fakultet Sarajevo', 0, 'ETF', 1, 3010, '06-4-1-'),
(2, 'Odsjek za računarstvo i informatiku', 1, 'RI', 0, 0, ''),
(3, 'Odsjek za automatiku i elektroniku', 1, 'AE', 0, 0, ''),
(4, 'Odsjek za elektroenergetiku', 1, 'EE', 0, 0, ''),
(5, 'Odsjek za telekomunikacije', 1, 'TK', 0, 0, '');


--
-- Dumping data for table `kanton`
-- Šifrarnik
--

INSERT INTO `kanton` (`id`, `naziv`, `kratki_naziv`) VALUES
(1, 'Bosansko-Podrinjski kanton', 'BPK'),
(2, 'Hercegovačko-Neretvanski kanton', 'HNK'),
(3, 'Livanjski kanton', 'LK'),
(4, 'Posavski kanton', 'PK'),
(5, 'Sarajevski kanton', 'SK'),
(6, 'Srednjobosanski kanton', 'SBK'),
(7, 'Tuzlanski kanton', 'TK'),
(8, 'Unsko-Sanski kanton', 'USK'),
(9, 'Zapadno-Hercegovački kanton', 'ZHK'),
(10, 'Zeničko-Dobojski kanton', 'ZDK'),
(11, 'Republika Srpska', 'RS'),
(12, 'Distrikt Brčko', 'DB'),
(13, 'Strani državljanin', 'SD');


--
-- Dumping data for table `tipkomponente`
-- Šifrarnik
--

INSERT INTO `tipkomponente` (`id`, `naziv`, `opis_opcija`) VALUES
(1, 'Ispit', ''),
(2, 'Integralni ispit', 'Ispiti koje zamjenjuje (razdvojeni sa +)'),
(3, 'Zadaće', ''),
(4, 'Prisustvo', 'Minimalan broj izostanaka (0=linearno)'),
(5, 'Fiksna', '');


--
-- Dumping data for table `komponenta`
-- Default sistem bodovanja za predmete (vidjeti tabele tippredmeta
-- i tippredmeta_komponenta)
--

INSERT INTO `komponenta` (`id`, `naziv`, `gui_naziv`, `kratki_gui_naziv`, `tipkomponente`, `maxbodova`, `prolaz`, `opcija`, `uslov`) VALUES
(1, 'I parcijalni', 'I parcijalni', 'I parc', 1, 20, 10, '', 0),
(2, 'II parcijalni', 'II parcijalni', 'II parc', 1, 20, 10, '', 0),
(3, 'Integralni', 'Integralni', 'Int', 2, 40, 20, '1+2', 0),
(4, 'Usmeni', 'Usmeni', 'Usmeni', 1, 40, 0, '', 0),
(5, 'Prisustvo', 'Prisustvo', 'Prisustvo', 3, 10, 0, '3', 0),
(6, 'Zadaće', 'Zadaće', 'Zadaće', 4, 10, 0, '', 0);


--
-- Dumping data for table `log2_dogadjaj`
-- Šifrarnik
-- Ovaj šifrarnik se automatski generiše, ali nije loše imati
-- standardne kodove događaja
--

INSERT INTO `log2_dogadjaj` (`id`, `opis`, `nivo`) VALUES
(1, 'prisustvo azurirano', 2),
(2, 'sesija istekla', 3),
(3, 'poslana zadaca (textarea)', 2),
(4, 'login', 1),
(5, 'logout', 1),
(6, 'sintaksna greska', 3),
(7, 'nepoznat korisnik', 3),
(8, 'pogresna sifra', 3),
(9, 'ne postoji fajl za zadacu', 3),
(10, 'uradio kviz', 2),
(11, 'poslana poruka', 2),
(12, 'student ne slusa predmet', 3),
(13, 'poslao praznu zadacu', 2),
(14, 'SQL greska', 3),
(15, 'isteklo vrijeme za slanje zadace', 3),
(16, 'poslana zadaca (attachment)', 2),
(17, 'greska pri slanju zadace (attachment)', 3),
(18, 'prisustvo - istekla sesija', 3),
(19, 'dodao fajl na projektu', 2),
(20, 'svi projekti su jos otkljucani', 3),
(21, 'nepostojeci modul', 3),
(22, 'nepoznat predmet', 3),
(23, 'registrovan cas', 2),
(24, 'uredio fajl na projektu', 2),
(25, 'obrisao fajl na projektu', 2),
(26, 'dodao link na projektu', 2),
(27, 'promijenjen datum ocjene', 4),
(28, 'dodana ocjena', 4),
(29, 'ponisten datum za izvoz', 2),
(30, 'bodovanje zadace', 2),
(31, 'student upisan na predmet (manuelno)', 4),
(32, 'student ispisan sa predmeta (manuelno)', 4),
(33, 'obrisan cas', 2),
(34, 'nije saradnik na predmetu', 3),
(35, 'preimenovana labgrupa', 2),
(36, 'vrijeme isteklo', 3),
(37, 'kreirana nova zadaca', 2),
(38, 'azurirana zadaca', 2),
(39, 'dodan novi autotest', 2),
(40, 'izmijenjen autotest', 2),
(41, 'dodan komentar na studenta', 2),
(42, 'vec popunjavan kviz', 3),
(43, 'zatrazena promjena licnih podataka', 2),
(44, 'dodao fajl na zavrsni', 2),
(45, 'azuriran sazetak zavrsnog rada', 2),
(46, 'obrisan fajl za zavrsni rad', 2),
(47, 'obrisan komentar', 2),
(48, 'promijenjena grupa studenta', 2),
(49, 'student ispisan sa grupe', 2),
(50, 'student upisan u grupu', 2),
(51, 'predmet nema virtuelnu grupu', 3),
(52, 'niko nije poslao zadacu', 3),
(53, 'nije nastavnik na predmetu', 3),
(54, 'kreirao ugovor o ucenju', 2),
(55, 'dodana tema zavrsnog rada', 2),
(56, 'izbrisana tema zavrsnog rada', 2),
(57, 'nova poruka poslana', 2),
(58, 'obrisana poruka', 2),
(59, 'nepostojeca poruka', 3),
(60, 'izmjena bodova za fiksnu komponentu', 2),
(61, 'izmijenjen kviz', 2),
(62, 'prihvacen zahtjev za promjenu podataka', 2),
(63, 'upisan rezultat ispita', 2),
(64, 'kreiran novi ispitni termin', 2),
(65, 'nepostojeci predmet', 3),
(66, 'zatrazeno brisanje slike', 2),
(67, 'odbijen zahtjev za promjenu podataka', 2),
(68, 'ispit - vrijednost &gt; max', 3),
(69, 'citanje fajla za attachment nije uspjelo', 3),
(70, 'pogresan tip datoteke', 3),
(71, 'izmjena ocjene', 4),
(72, 'nastavniku data prava na predmetu', 4),
(73, 'korisnik pokusao pristupiti modulu za koji nema permisije', 3),
(74, 'nije studentska, a pristupa tudjem izvjestaju', 3),
(75, 'student nije u odgovarajucoj labgrupi', 3),
(76, 'pristup nedozvoljenom modulu', 3),
(77, 'nepoznata akademska godina', 3),
(78, 'aktiviran studentski modul', 2),
(79, 'izmijenjeni parametri projekata na predmetu', 2),
(80, 'dodao projekat', 2),
(81, 'student prijavljen na projekat', 2),
(82, 'ne postoji komponenta za zadace', 3),
(83, 'kreiran novi ispit', 4),
(84, 'prijavljen na termin', 2),
(85, 'odjavljen sa termina', 2),
(86, 'email adresa dodana', 2),
(87, 'ima ogranicenje na labgrupu', 3),
(88, 'izmijenjen ispitni termin', 2),
(89, 'obrisan ispit', 4),
(90, 'izmijenio temu zavrsnog rada', 2),
(91, 'csrf token nije dobar', 3),
(92, 'pristupa tudjoj slici a student je', 3),
(93, 'deaktiviran studentski modul', 2),
(94, 'nije na projektu', 3),
(95, 'ispit - istekla sesija', 3),
(96, 'izmjenjen rezultat ispita', 2),
(97, 'obrisana ocjena', 4),
(98, 'ispit - datum konacne ocjene nije u trazenom formatu', 3),
(99, 'izbrisan rezultat ispita', 2),
(100, 'kreiran novi termin za prijemni ispit', 2),
(101, 'kreirana labgrupa', 2),
(102, 'kreirana labgrupa (masovni unos)', 2),
(103, 'student upisan u grupu (masovni unos)', 2),
(104, 'obrisana labgrupa', 2),
(105, 'nepostojeca labgrupa (brisanje)', 3),
(106, 'izbrisan ispitni termin', 2),
(107, 'promijenjen datum ispita', 2),
(108, 'id termina i ispita se ne poklapaju', 3),
(109, 'brzo unesen kandidat za prijemni', 2),
(110, 'upisana nova srednja skola', 2),
(111, 'ispit - konacna ocjena manja od 6', 3),
(112, 'izbrisan termin ispita', 2),
(113, 'brisem osobu sa prijemnog', 2),
(114, 'korisnik nije pronadjen na LDAPu', 3),
(115, 'dodan novi korisnik', 4),
(116, 'promijenjeni licni podaci korisnika', 2),
(117, 'izmjena kandidata za prijemni', 2),
(118, 'kreiran tip predmeta', 2),
(119, 'iskljucio savjet dana', 2),
(120, 'obrisana zadaca', 4),
(121, 'student ne slusa predmet za zadacu', 3),
(122, 'novi kandidat za prijemni', 2),
(123, 'poziv zamgerlog2 funkcije nije ispravan', 3),
(136, 'upisano novo mjesto rodjenja', 2),
(137, 'promjena opcine / statusa domacinstva za skolu', 2),
(138, 'promijenjen tip predmeta', 4),
(139, 'upisano novo mjesto (adresa)', 2),
(140, 'promjena opcine/drzave za mjesto rodjenja', 2),
(141, 'dodan novi login za korisnika', 2),
(142, 'nije uspjelo brisanje fajla za zavrsni', 3),
(143, 'id fajla nepostojeci ili ne odgovara zavrsnom', 3),
(144, 'nepostojeci file na zavrsnom radu', 3),
(145, 'citanje fajla za attachment nije uspjelo - zavrsni', 3),
(146, 'kreirao zahtjev za koliziju', 2),
(147, 'nisu definisani kriteriji za upis', 3),
(148, 'promijenjeni kriteriji za prijemni ispit', 4),
(149, 'student upisan na studij', 4),
(150, 'izmijenjen login za korisnika', 4),
(151, 'nema pravo pristupa poruci', 3),
(152, 'osoba nije kandidat na prijemnom', 3),
(153, 'ne postoji obrazac za osobu', 3),
(154, 'upisan rezultat na prijemnom', 2),
(155, 'email adresa obrisana', 2),
(156, 'email adresa promijenjena', 2),
(157, 'nepostojeci ispit ili nije sa predmeta', 3),
(158, 'nastavnik angazovan na predmetu', 4),
(159, 'ispit - datum konacne ocjene je nemoguc', 3),
(160, 'dodao biljesku na zavrsni rad', 2),
(161, 'proglasen za studenta', 4),
(162, 'postavljen broj indeksa', 4),
(163, 'student ispisan sa studija', 4),
(164, 'student upisan na predmet (obavezan)', 4),
(165, 'student ispisan sa predmeta (ispis sa studija)', 4),
(166, 'nepostojeca osoba', 3),
(167, 'nastavnik deangazovan sa predmeta', 4),
(168, 'citanje fajla za attachment nije uspjelo - zadaca', 3),
(169, 'promijenjen tip ispita', 4),
(170, 'nastavniku oduzeta prava na predmetu', 4),
(171, 'osobi data privilegija', 4),
(172, 'student upisan na predmet (izborni)', 4),
(173, 'kreirana nova anketa', 4),
(174, 'promijenjeni podaci za anketu', 4),
(175, 'aktivirana anketa', 4),
(176, 'azurirani podaci o izboru', 2),
(177, 'greska prilikom slanja fajla na zavrsni', 3),
(178, 'student upisan na predmet (preneseni)', 4),
(179, 'kreirao ponudu kursa zbog studenta', 4),
(180, 'izmijenjena poruka', 2),
(181, 'student upisan na predmet (kolizija)', 4),
(182, 'prihvacen zahtjev za koliziju', 4),
(183, 'student ispisan sa predmeta', 4),
(184, 'id studenta i predmeta ne odgovaraju', 3),
(185, 'dodana stavka u raspored', 2),
(186, 'dodan zahtjev za promjenu odsjeka', 2),
(187, 'osoba nema sliku', 3),
(188, 'kopiranje sa predmeta na kojem nema grupa', 3),
(189, 'prekopirane labgrupe', 2),
(190, 'nijedna zadaca nije aktivna', 3),
(191, 'zatrazeno postavljanje/promjena slike', 2),
(192, 'nepoznat id zahtjeva za promjenu podataka', 3),
(193, 'kreiran raspored', 2),
(194, 'ažurirana stavka u rasporedu', 2),
(195, 'citanje fajla za attachment nije uspjelo - postavka', 3),
(196, 'kreirana labgrupa (kopiranje)', 2),
(197, 'student upisan u grupu (kopiranje)', 2),
(198, 'izmijenio projekat', 2),
(199, 'prijavljen na projekat', 2),
(200, 'izbrisan projekat', 2),
(201, 'obrisan autotest', 2),
(202, 'izmijenjena ogranicenja nastavniku', 4),
(203, 'student prebacen na projekat', 2),
(204, 'student ispisan sa grupe (brisanje)', 2),
(205, 'odjavljen sa projekta', 2),
(206, 'postavljena slika za korisnika', 2),
(207, 'pokusao ispisati studenta sa studija koji ne slusa', 3),
(208, 'ne postoji attachment', 3),
(209, 'student ispisan sa grupe (masovni unos)', 2),
(210, 'poruka izmijenjena', 2),
(211, 'student odjavljen sa projekta', 2),
(212, 'odjavljen sa starog projekta', 2),
(213, 'nepostojeca labgrupa', 3),
(214, 'tekst poruke je prekratak', 3),
(215, 'korisnik vec postoji u bazi', 3),
(216, 'uputio novi zahtjev za potvrdu', 2),
(217, 'odustao od zahtjeva za potvrdu', 2),
(218, 'korisnik nikada nije studirao', 3),
(219, 'obradjen zahtjev za potvrdu', 2),
(220, 'dodao temu na projektu', 2),
(221, 'obrisan zahtjev za potvrdu', 2),
(222, 'privilegije', 3),
(223, 'deaktivirana anketa', 4),
(224, 'kopiranje grupa sa istog predmeta', 3),
(225, 'webide nedozvoljen pristup', 3),
(226, 'id zadace pogresan', 3),
(227, 'zadaca i ponudakursa ne odgovaraju', 3),
(228, 'nije nastavnik na predmetu za zadacu', 3),
(229, 'zadaca nema toliko zadataka', 3),
(230, 'promijenjene zamger opcije', 2),
(231, 'ispit - pogresne privilegije', 3),
(232, 'obrisana postavka zadace', 2),
(233, 'postavka ne postoji', 3),
(234, 'obrisan zahtjev za promjenu odsjeka', 2),
(235, 'upisana nova nacionalnost', 2),
(236, 'id zadace i predmeta se ne poklapaju', 3),
(237, 'izmijenjeni podaci o predmetu', 4),
(238, 'korisnik nema nikakve privilegije', 3),
(239, 'uspjesno popunjena anketa', 2),
(240, 'anketa vec popunjena', 3),
(241, 'ilegalan hash code', 3),
(242, 'ilegalan CSRF token', 3),
(243, 'nije definisan predmet a korisnik je logiran', 3),
(244, 'preview ankete privilegije', 3),
(245, 'ag je sada', 2),
(246, 'predmet je', 2),
(247, 'naziv nije ispravan', 3),
(248, 'pokusao ispisati studenta sa semestra koji ne slusa', 3),
(249, 'student pristupa komentarima', 2),
(250, 'id ankete i godine ne odgovaraju', 2),
(251, 'nepostojeca anketa', 2),
(252, 'ispit - vrijednost nije ni broj ni /', 2),
(253, 'dodan kviz', 2),
(254, 'nepostojeca virtualna labgrupa', 2),
(255, 'dodano pitanje na kviz', 2),
(256, 'dodan odgovor na pitanje', 2),
(257, 'izmijenjeno pitanje na kvizu', 2),
(258, 'obrisan odgovor sa kviza', 2),
(259, 'dodan uslov za autotest', 2),
(260, 'izmijenjen uslov za autotest', 2),
(261, 'odgovor proglasen za (ne)tacan', 2),
(262, 'prisustvo - nepostojeci cas', 3),
(263, 'poslao popunjen kviz a nema stavke u student_kviz', 3),
(264, 'nema pravo pristupa zavrsnom radu', 3),
(265, 'popunjava alumni anketu a nije master', 3),
(266, 'popunjava alumni anketu a nije zavrsio master', 3),
(267, 'nepoznat student', 3),
(268, 'uredio link na projektu', 2),
(269, 'citanje fajla za attachment nije uspjelo - projekat', 3),
(270, 'nepostojeci file na projektu', 3),
(271, 'pristup nepostojecem kvizu', 3),
(272, 'student nije na predmetu', 3),
(273, 'nepostojeci rad', 3),
(274, 'nepostojeca zadaca', 3),
(275, 'dodani podaci o izboru', 2),
(276, 'nepoznat ispit', 3),
(277, 'dodana ponuda kursa na predmet', 4),
(278, 'obrisana ponudakursa', 4),
(279, 'pokusao ograniciti sve grupe nastavniku', 3),
(280, 'kreirana virtuelna labgrupa', 2),
(281, 'nije pronadjena ponudakursa', 3),
(282, 'promijenjena ponuda kursa za studenta', 4),
(283, 'ispisujem studenta sa dvostruke ponude kursa', 4),
(284, 'student upisan na predmet', 4),
(285, 'promijenjena a.g. ocjene', 2),
(286, 'student ne slusa predmet (ispis)', 3),
(287, 'pristup nepostojećoj anketi', 3),
(288, 'nije ponudjen predmet', 3),
(289, 'osobi oduzeta privilegija', 4),
(290, 'obrisan login za korisnika', 4),
(291, 'obrisan uslov za autotest', 2),
(292, 'pristup nepostojecoj poruci', 3),
(293, 'pokusao ispisati studenta koji nije upisan', 3),
(294, 'obrisano pitanje sa kviza', 2),
(295, 'smanjen broj zadataka u zadaci', 2),
(296, 'prekopirana pitanja sa kviza', 2),
(297, 'ispit - nepoznat ispit ili nije saradnik', 3),
(298, 'projekat popunjen', 3),
(299, 'pretraga - istekla sesija', 3),
(300, 'prijemni - istekla sesija', 3),
(301, 'popunjen kapacitet ekstra za predmet', 3),
(302, 'popunjen kapacitet za predmet', 3),
(303, 'iskopirani autotestovi', 2),
(304, 'nepostojeca tema zavrsnog rada', 3),
(305, 'kreiran novi predmet', 4),
(306, 'autotestiran student', 2),
(307, 'spoofing autotesta', 3),
(308, 'login ne postoji na LDAPu', 3),
(309, 'dodao temu zavrsnog rada', 2),
(310, 'id zavrsnog rada i predmeta se ne poklapaju', 3),
(311, 'neispravni parametri', 3),
(312, 'nije studentska', 3),
(313, 'nije nastavnik', 3),
(314, 'student ispisan sa grupe (kopiranje)', 2),
(315, 'pokusaj slanja/izmjene poruke sa opsegom', 3),
(316, 'poruka ima nepoznat opseg', 3),
(317, 'poruka ima nepoznatog primaoca (opseg: godina studija)', 3),
(318, 'nedostaje slog u tabeli akademska_godina_predmet', 3),
(319, 'obrisana slika za korisnika', 2);


--
-- Dumping data for table `mjesto`
-- Neka mjesta (šifrarnik se automatski popunjava)
--

INSERT INTO `mjesto` (`id`, `naziv`, `opcina`, `drzava`) VALUES
(1, 'Sarajevo', 0, 1),
(2, 'Sarajevo', 13, 1),
-- Sarajevo je mjesto koje se prostire na vise opcina,
-- ali dodajemo i varijantu sa opcinom Centar radi oznacavanja
-- mjesta rodjenja
(3, 'Zenica', 77, 1),
(4, 'Mostar', 46, 1),
(5, 'Banja Luka', 93, 1),
(6, 'Bihać', 2, 1),
(7, 'Tuzla', 69, 1);


--
-- Dumping data for table `nacin_studiranja`
-- Šifrarnik
--

INSERT INTO `nacin_studiranja` (`id`, `naziv`, `moguc_upis`) VALUES
(1, 'Redovan', 1),
(2, 'Paralelan', 0),
(3, 'Redovan samofinansirajući', 1),
(4, 'Vanredan', 1),
(5, 'DL', 1),
(6, 'Mobilnost', 0);


--
-- Dumping data for table `nacionalnost`
-- Šifrarnik
--

INSERT INTO `nacionalnost` (`id`, `naziv`) VALUES
(1, 'Bošnjak/Bošnjakinja'),
(2, 'Srbin/Srpkinja'),
(3, 'Hrvat/Hrvatica'),
(4, 'Rom/Romkinja'),
(5, 'Ostalo'),
(6, 'Nepoznato / Nije se izjasnio/la');


--
-- Dumping data for table `naucni_stepen`
-- Šifrarnik
--

INSERT INTO `naucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'Doktor nauka', 'dr'),
(2, 'Magistar nauka', 'mr'),
(6, 'Bez naučnog stepena', '');


--
-- Dumping data for table `opcina`
-- Šifrarnik
--

INSERT INTO `opcina` (`id`, `naziv`) VALUES
(1, 'Banovići'),
(2, 'Bihać'),
(3, 'Bosanska Krupa'),
(4, 'Bosanski Petrovac'),
(5, 'Bosansko Grahovo'),
(6, 'Breza'),
(7, 'Bugojno'),
(8, 'Busovača'),
(9, 'Bužim'),
(10, 'Čapljina'),
(11, 'Cazin'),
(12, 'Čelić'),
(13, 'Centar, Sarajevo'),
(14, 'Čitluk'),
(15, 'Drvar'),
(16, 'Doboj Istok'),
(17, 'Doboj Jug'),
(18, 'Dobretići'),
(19, 'Domaljevac-Šamac'),
(20, 'Donji Vakuf'),
(21, 'Foča-Ustikolina'),
(22, 'Fojnica'),
(23, 'Glamoč'),
(24, 'Goražde'),
(25, 'Gornji Vakuf-Uskoplje'),
(26, 'Gračanica'),
(27, 'Gradačac'),
(28, 'Grude'),
(29, 'Hadžići'),
(30, 'Ilidža'),
(31, 'Ilijaš'),
(32, 'Jablanica'),
(33, 'Jajce'),
(34, 'Kakanj'),
(35, 'Kalesija'),
(36, 'Kiseljak'),
(37, 'Kladanj'),
(38, 'Ključ'),
(39, 'Konjic'),
(40, 'Kreševo'),
(41, 'Kupres'),
(42, 'Livno'),
(43, 'Ljubuški'),
(44, 'Lukavac'),
(45, 'Maglaj'),
(46, 'Mostar'),
(47, 'Neum'),
(48, 'Novi Grad, Sarajevo'),
(49, 'Novo Sarajevo'),
(50, 'Novi Travnik'),
(51, 'Odžak'),
(52, 'Olovo'),
(53, 'Orašje'),
(54, 'Pale-Prača'),
(55, 'Posušje'),
(56, 'Prozor-Rama'),
(57, 'Ravno'),
(58, 'Sanski Most'),
(59, 'Sapna'),
(60, 'Široki Brijeg'),
(61, 'Srebrenik'),
(62, 'Stari Grad, Sarajevo'),
(63, 'Stolac'),
(64, 'Teočak'),
(65, 'Tešanj'),
(66, 'Tomislavgrad'),
(67, 'Travnik'),
(68, 'Trnovo (FBiH)'),
(69, 'Tuzla'),
(70, 'Usora'),
(71, 'Vareš'),
(72, 'Velika Kladuša'),
(73, 'Visoko'),
(74, 'Vitez'),
(75, 'Vogošća'),
(76, 'Zavidovići'),
(77, 'Zenica'),
(78, 'Žepče'),
(79, 'Živinice'),
(80, 'Berkovići'),
(81, 'Bijeljina'),
(82, 'Bileća'),
(83, 'Bosanska Kostajnica'),
(84, 'Bosanski Brod'),
(85, 'Bratunac'),
(86, 'Čajniče'),
(87, 'Čelinac'),
(88, 'Derventa'),
(89, 'Doboj'),
(90, 'Donji Žabar'),
(91, 'Foča'),
(92, 'Gacko'),
(93, 'Banja Luka'),
(94, 'Gradiška'),
(95, 'Han Pijesak'),
(96, 'Istočni Drvar'),
(97, 'Istočna Ilidža'),
(98, 'Istočni Mostar'),
(99, 'Istočni Stari Grad'),
(100, 'Istočno Novo Sarajevo'),
(101, 'Jezero'),
(102, 'Kalinovik'),
(103, 'Kneževo'),
(104, 'Kozarska Dubica'),
(105, 'Kotor Varoš'),
(106, 'Krupa na Uni'),
(107, 'Kupres (RS)'),
(108, 'Laktaši'),
(109, 'Ljubinje'),
(110, 'Lopare'),
(111, 'Milići'),
(112, 'Modriča'),
(113, 'Mrkonjić Grad'),
(114, 'Nevesinje'),
(115, 'Novi Grad (RS)'),
(116, 'Novo Goražde'),
(117, 'Osmaci'),
(118, 'Oštra Luka'),
(119, 'Pale'),
(120, 'Pelagićevo'),
(121, 'Petrovac'),
(122, 'Petrovo'),
(123, 'Prijedor'),
(124, 'Prnjavor'),
(125, 'Ribnik'),
(126, 'Rogatica'),
(127, 'Rudo'),
(128, 'Šamac'),
(129, 'Šekovići'),
(130, 'Šipovo'),
(131, 'Sokolac'),
(132, 'Srbac'),
(133, 'Srebrenica'),
(134, 'Teslić'),
(135, 'Trebinje'),
(136, 'Trnovo (RS)'),
(137, 'Ugljevik'),
(138, 'Višegrad'),
(139, 'Vlasenica'),
(140, 'Vukosavlje'),
(141, 'Zvornik'),
(142, 'Brčko'),
(143, '(nije u BiH)');


--
-- Dumping data for table `posebne_kategorije`
-- Šifrarnik
--

INSERT INTO `posebne_kategorije` (`id`, `naziv`) VALUES
(1, 'Djeca šehida i poginulih boraca'),
(2, 'Djeca ratnih vojnih invalida'),
(3, 'Djeca demobilisanih boraca'),
(4, 'Djeca nosilaca ratnih priznanja'),
(5, 'Djeca bez oba roditelja');


--
-- Dumping data for table `preference`
-- Molim vas da ovo ažurirate svaki put kad se promijeni šema baze
--

INSERT INTO `preference` (`korisnik`, `preferenca`, `vrijednost`) VALUES
(0, 'verzija-baze', '1507557842');


--
-- Dumping data for table `privilegije`
-- Šifrarnik
--

INSERT INTO `privilegije` (`osoba`, `privilegija`) VALUES
(1, 'siteadmin'),
(1, 'studentska'),
(1, 'student'),
(1, 'nastavnik');


--
-- Dumping data for table `programskijezik`
-- Šfrarnik
--

INSERT INTO `programskijezik` (`id`, `naziv`, `geshi`, `ekstenzija`, `ace`, `kompajler`, `opcije_kompajlera`, `opcije_kompajlera_debug`, `debugger`, `profiler`, `opcije_profilera`) VALUES
(0, '---Nije odre&#273;en---', '', '', '', '', '', '', '', '', ''),
(1, 'C', 'C', '.c', 'c_cpp', 'gcc', '-O1 -Wall -Wuninitialized -Winit-self -Wno-unused-result -Wfloat-equal -Wno-sign-compare -Werror=implicit-function-declaration -Werror=vla -pedantic -lm -pass-exit-codes', '-ggdb -lm -pass-exit-codes', 'gdb', 'valgrind', '--leak-check=full'),
(2, 'C++', 'C++', '.cpp', 'c_cpp', 'g++', '-O1 -Wall -Wuninitialized -Winit-self -Wfloat-equal -Wno-sign-compare -Werror=implicit-function-declaration -Werror=vla -pedantic -lm -pass-exit-codes', '-ggdb -lm -pass-exit-codes', 'gdb', 'valgrind', '--leak-check=full'),
(3, 'Java', 'Java', '.java', 'java', 'javac', '-encoding cp1250', '', '', '', ''),
(4, 'Matlab .m', 'Matlab M', '.m', '', '', '', '', '', '', ''),
(5, 'HTML', 'HTML', '.html', 'html', '', '', '', '', '', ''),
(6, 'PHP', 'PHP', '.php', 'php', '', '', '', '', '', ''),
(7, 'C++11', 'C++', '.cpp', 'c_cpp', 'g++', '-std=c++11 -O1 -Wall -Wuninitialized -Winit-self -Wfloat-equal -Wno-sign-compare -Werror=implicit-function-declaration -Werror=vla -pedantic -lm -pass-exit-codes', '-std=c++11 -ggdb -lm -pass-exit-codes', 'gdb', 'valgrind', '--leak-check=full'),
(8, 'JavaScript', 'JAVASCRIPT', '.js', '', '', '', '', '', '', ''),
(9, 'Python', 'Python', '.py', 'python', 'python3', '', '', '', '', '');


--
-- Dumping data for table `savjet_dana`
-- Savjeti za korisnike
--

INSERT INTO `savjet_dana` (`id`, `tekst`, `vrsta_korisnika`) VALUES
(1, '<p>...da je Charles Babbage, matematičar i filozof iz 19. vijeka za kojeg se smatra da je otac ideje prvog programabilnog računara, u svojoj biografiji napisao:</p>\r\n\r\n<p><i>U dva navrata su me pitali</i></p>\r\n\r\n<p><i>"Molim Vas gospodine Babbage, ako u Vašu mašinu stavite pogrešne brojeve, da li će izaći tačni odgovori?"</i></p>\r\n\r\n<p><i>Jednom je to bio pripadnik Gornjeg, a jednom Donjeg doma. Ne mogu da potpuno shvatim tu vrstu konfuzije ideja koja bi rezultirala takvim pitanjem.</i></p>', 'nastavnik'),
(2, '<p>...da sada možete podesiti sistem bodovanja na vašem predmetu (broj bodova koje studenti dobijaju za ispite, prisustvo, zadaće, seminarski rad, projekte...)?</p>\r\n<ul><li>Kliknite na dugme [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite opciju <i>Sistem bodovanja</i>.</li>\r\n<li>Slijedite uputstva.</li></ul>\r\n<p><b>Važna napomena:</b> Promjena sistema bodovanja može dovesti do gubitka do sada upisanih bodova na predmetu!</p>', 'nastavnik'),
(3, '<p>...da možete pristupiti Dosjeu studenta sa svim podacima koji se tiču uspjeha studenta na datom predmetu? Dosje studenta sadrži, između ostalog:</p>\r\n<ul><li>Fotografiju studenta;</li>\r\n<li>Koliko puta je student ponavljao predmet, da li je u koliziji, da li je prenio predmet na višu godinu;</li>\r\n<li>Sve podatke sa pogleda grupe (prisustvo, zadaće, rezultati ispita, konačna ocjena) sa mogućnošću izmjene svakog podatka;</li>\r\n<li>Za ispite i konačnu ocjenu možete vidjeti dnevnik izmjena sa informacijom ko je i kada izmijenio podatak.</li>\r\n<li>Brze linkove na dosjee istog studenta sa ranijih akademskih godina (ako je ponavljao/la predmet).</li></ul>\r\n\r\n<p>Dosjeu studenta možete pristupiti tako što kliknete na ime studenta u pregledu grupe. Na vašem početnom ekranu kliknite na ime grupe ili link <i>(Svi studenti)</i>, a zatim na ime i prezime studenta.</p>\r\n	\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.
</i></p>', 'nastavnik'),
(4, '<p>...da možete ostavljati kratke tekstualne komentare na rad studenata?</p>\r\n<p>Na vašem početnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>. Zatim kliknite na ikonu sa oblačićem pored imena studenta:<br>\r\n<img src="static/images/16x16/comment_blue.png" width="16" height="16"></p>\r\n<p>Možete dobiti pregled studenata sa komentarima na sljedeći način:<br>\r\n<ul><li>Pored naziva predmeta kliknite na link [EDIT].</li>\r\n<li>Zatim s lijeve strane kliknite na link <i>Izvještaji</i>.</li>\r\n<li>Konačno, kliknite na opciju <i>Spisak studenata</i> - <i>Sa komentarima na rad</i>.</li></ul>\r\n<p>Na istog studenta možete ostaviti više komentara pri čemu je svaki komentar datiran i označeno je ko ga je ostavio.</p>	\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 7-8.</i></p>', 'nastavnik'),
(5, '<p>...da možete brzo i lako pomoću nekog spreadsheet programa (npr. MS Excel) kreirati grupe na predmetu?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="static/images/32x32/excel.png" width="32" height="32"><br>\r\nDobićete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte naziv grupe npr. "Grupa 1" (bez navodnika). Koristite Copy i Paste opcije Excela da biste brzo definisali grupu za sve studente.</li>\r\n<li>Kada završite definisanje grupa, koristeći tipku Shift i tipke sa strelicama označite imena studenata i imena grupa. Nemojte označiti naslov niti redni broj. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga 
otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja. Sada s lijeve strane izaberite opciju <i>Grupe za predavanja i vježbe</i>.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos studenata u grupe</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li></ul>\r\n<p>Ovim su grupe kreirane!</p>\r\n\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 16.</i></p>', 'nastavnik'),
(6, '<p>...da možete brzo i lako ocijeniti zadaću svim studentima na predmetu ili u grupi, koristeći neki spreadsheet program (npr. MS Excel)?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, a s desne izaberite izvještaj <i>Spisak studenata</i> - <i>Bez grupa</i>. Alternativno, ako želite unositi ocjene samo za jednu grupu, možete koristiti izvještaj <i>Jedna kolona po grupama</i> pa u Excelu pobrisati sve grupe osim one koja vas interesuje.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="static/images/32x32/excel.png" width="32" height="32"></li>\r\n<li>Pored imena svakog studenta nalazi se broj indeksa. <b>Umjesto broja indeksa</b> upišite broj bodova ostvarenih na određenom zadatku određene zadaće.</li>\r\n<li>Korištenjem tipke Shift i tipki sa strelicama izaberite samo imena studenata i bodove. Nemojte selektovati naslov ili redne brojeve. Držeći tipku Ctrl pritisnite tipku C.</li>\r\n<li>Vratite 
se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja. Sada s lijeve strane izaberite opciju <i>Kreiranje i unos zadaća</i>.</li>\r\n<li>Uvjerite se da je na spisku <i>Postojeće zadaće</i> definisana zadaća koju želite unijeti. Ako nije, popunite formular ispod naslova <i>Kreiranje zadaće</i> sa odgovarajućim podacima.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos zadaća</i> i pritisnite Ctrl+V. Trebalo bi da ugledate raspored studenata po grupama unutar tekstualnog polja.</li>\r\n<li>U polju <i>Izaberite zadaću</i> odaberite upravo kreiranu zadaću. Ako zadaća ima više zadataka, u polju <i>Izaberite zadatak</i> odaberite koji zadatak masovno unosite.\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> a pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>
\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme <i>Potvrda</i>.</li>\r\n<li>Ovu proceduru sada vrlo lako možete ponoviti za sve zadatke i sve zadaće zato što već imate u Excelu sve podatke osim broja bodova.</li></ul>\r\n<p>Ovim su rezultati zadaće uneseni za sve studente!</p>\r\n\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 27-28.</i></p>', 'nastavnik'),
(12, '<p>...da možete ograničiti format datoteke u kojem studenti šalju zadaću?</p>\r\n<p>Prilikom kreiranja nove zadaće, označite opciju pod nazivom <i>Slanje zadatka u formi attachmenta</i>. Pojaviće se spisak tipova datoteka koje studenti mogu koristiti prilikom slanja zadaće u formi attachmenta.</p>\r\n<p>Izaberite jedan ili više formata kako bi studenti dobili grešku u slučaju da pokušaju poslati zadaću u nekom od formata koje niste izabrali. Ako ne izaberete nijednu od ponuđenih opcija, biće dozvoljeni svi formati datoteka, uključujući i one koji nisu navedeni na spisku.</p>\r\n\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 26-27.</i></p>', 'nastavnik'),
(7, '<p>...da možete preuzeti odjednom sve zadaće koje su poslali studenti u grupi u formi ZIP fajla, pri čemu su zadaće imenovane po sistemu Prezime_Ime_BrojIndeksa?</p>\r\n<ul><li>Na vašem početnom ekranu kliknite na ime grupe ili na link <i>(Svi studenti)</i>.</li>\r\n<li>U zaglavlju tabele sa spiskom studenata možete vidjeti navedene zadaće: npr. Zadaća 1, Zadaća 2 itd.</li>\r\n<li>Ispod naziva svake zadaće nalazi se riječ <i>Download</i> koja predstavlja link - kliknite na njega.</li></ul>	\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 11-12.</i></p>', 'nastavnik'),
(8, '<p>...da možete imati više termina jednog ispita? Pri tome se datum termina ne mora poklapati sa datumom ispita.</p>\r\n<p>Datum ispita se daje samo okvirno, kako bi se po nečemu razlikovali npr. junski rok i septembarski rok. Datum koji studentu piše na prijavi je datum koji pridružite terminu za prijavu ispita.</p>\r\n<p>Da biste definisali termine ispita:</p>\r\n<ul><li>Najprije kreirajte ispit, tako što ćete kliknuti na link [EDIT] a zatim izabrati opciju Ispiti s lijeve strane. Zatim popunite formular ispod naslova <i>Kreiranje novog ispita</i>.</li>\r\n<li>U tabeli ispita možete vidjeti novi ispit. Desno od ispita možete vidjeti link <i>Termini</i>. Kliknite na njega.</li>\r\n<li>Zatim kreirajte proizvoljan broj termina popunjavajući formular ispod naslova <i>Registrovanje novog termina</i>.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, poglavlje "Prijavljivanje za ispit" (str. 21-26).</i></p>', 'nastavnik'),
(9, '<p>...da, u slučaju da se neki student nije prijavio/la za vaš ispit, možete ih manuelno prijaviti na termin kako bi imao/la korektan datum na prijavi?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta. S lijeve strane izaberite link <i>Ispiti</i>.</li>\r\n<li>U tabeli ispita locirajte ispit koji želite i kliknite na link <i>Termini</i> desno od željenog ispita.</li>\r\n<li>Ispod naslova <i>Objavljeni termini</i> izaberite željeni termin i kliknite na link <i>Studenti</i> desno od željenog termina.</li>\r\n<li>Sada možete vidjeti sve studente koji su se prijavili za termin. Pored imena i prezimena studenta možete vidjeti dugme <i>Izbaci</i> kako student više ne bi bio prijavljen za taj termin.</li>\r\n<li>Ispod tabele studenata možete vidjeti padajući spisak svih studenata upisanih na vaš predmet. Izaberite na padajućem spisku studenta kojeg želite prijaviti za termin i kliknite na dugme <i>Dodaj</i>.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" 
target="_new">Uputstvima za upotrebu</a>, str. 26.</i></p>', 'nastavnik'),
(10, '<p>...da upisom studenata na predmete u Zamgeru sada u potpunosti rukuje Studentska služba?</p>\r\n<p>Ako vam se pojavi student kojeg nemate na spiskovima u Zamgeru, recite mu da se <b>obavezno</b> javi u Studentsku službu, ne samo radi vašeg predmeta nego generalno radi regulisanja statusa (npr. neplaćenih školarina, taksi i slično).</p>', 'nastavnik'),
(11, '<p>...da svaki korisnik može imati jedan od tri nivoa pristupa bilo kojem predmetu:</p><ul><li><i>asistent</i> - može unositi prisustvo časovima i ocjenjivati zadaće</li><li><i>super-asistent</i> - može unositi sve podatke osim konačne ocjene</li><li><i>nastavnik</i> - može unositi i konačnu ocjenu.</li></ul><p>Početni nivoi pristupa se određuju na osnovu zvanično usvojenog nastavnog ansambla, a u slučaju da želite promijeniti nivo pristupa bez izmjena u ansamblu (npr. kako biste asistentu dali privilegije unosa rezultata ispita), kontaktirajte Studentsku službu.</p>\r\n\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 3-4.</i></p>', 'nastavnik'),
(13, '<p>...da možete utjecati na format u kojem se izvještaj prosljeđuje Excelu kada kliknete na Excel ikonu u gornjem desnom uglu izvještaja?<br>\r\n<img src="static/images/32x32/excel.png" width="32" height="32"></p>\r\n<p>Može se desiti da izvještaj ne izgleda potpuno kako treba u vašem spreadsheet programu. Podaci se šalju u CSV formatu pod pretpostavkom da koristite regionalne postavke za BiH (ili Hrvatsku ili Srbiju). Ako izvještaj u vašem programu ne izgleda kako treba, slijedi nekoliko savjeta kako možete utjecati na to.</p>\r\n<ul><li>Ako se svi podaci nalaze u jednoj koloni, vjerovatno je da koristite sistem sa Američkim regionalnim postavkama. U vašem Profilu možete pod Zamger opcije izabrati CSV separator "zarez" umjesto "tačka-zarez", ali vjerovatno je da vam naša slova i dalje neće izgledati kako treba.</li>\r\n<li>Moguće je da će dokument izgledati ispravno, osim slova sa afrikatima koja će biti zamijenjena nekim drugim. Na žalost, ne postoji način da se ovo riješi. Excel može učitati CSV datoteke 
isključivo u formatu koji ne podržava prikaz naših slova. Možete uraditi zamjenu koristeći Replace opciju vašeg programa. Nešto složenija varijanta je da koristite "Save Link As" opciju vašeg web preglednika, promijenite naziv dokumenta iz izvjestaj.csv u izvjestaj.txt, a zatim koristite <a href="http://office.microsoft.com/en-us/excel-help/text-import-wizard-HP010102244.aspx">Excel Text Import Wizard</a>.</li>\r\n<li>Ako koristite OpenOffice.org uredski paket, prilikom otvaranja dokumenta izaberite Text encoding "Eastern European (Windows-1250)", a kao razdjelnik (Delimiter) izaberite tačka-zarez (Semicolon). Ostale opcije obavezno isključite. Takođe isključite opciju spajanja razdjelnika (Merge delimiters).</li>\r\n<li>Može se desiti da vaš program prepozna određene stavke (npr. redne brojeve ili ostvarene bodove) kao datum, pogotovo ako ste poslušali savjet iz prve tačke - odnosno, ako ste kao CSV separator podesili "zarez".</li>\r\n<li>U velikoj većini slučajeva možete dobiti potpuno zadovoljavajuće 
rezultate ako otvorite prazan dokument u vašem spreadsheet programu (npr. Excel) i zatim napravite copy-paste kompletnog sadržaja web stranice.</li></ul>\r\n\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, strana 32-33.</i></p>', 'nastavnik'),
(14, '<p>...da možete brzo i lako pomoću nekog spreadsheet programa (npr. MS Excel) unijeti rezultate ispita ili konačne ocjene?</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta.</li>\r\n<li>S lijeve strane izaberite link <i>Izvještaji</i>, zatim s desne idite na <i>Spisak studenata</i> - <i>Bez grupa</i>. Ili, ako vam je lakše unositi podatke po grupama, izaberite izvještaj <i>Jedna kolona po grupama</i>.</li>\r\n<li>Kliknite na Excel ikonu u gornjem desnom uglu izvještaja:<br>\r\n<img src="static/images/32x32/excel.png" width="32" height="32"><br>\r\nDobićete spisak svih studenata na predmetu sa brojevima indeksa.</li>\r\n<li>Desno od imena studenta stoji broj indeksa. <i>Umjesto broja indeksa</i> ukucajte broj bodova koje je student ostvario na ispitu ili konačnu ocjenu.</li>\r\n<li>Kada završite unos rezultata/ocjena, koristeći tipku Shift i tipke sa strelicama označite imena studenata i ocjene. Nemojte označiti naslov niti redni broj studenta. Držeći tipku Ctrl pritisnite tipku C.</li>
\r\n<li>Vratite se na prozor Zamgera. Ako ste zatvorili Zamger - ponovo ga otvorite, prijavite se i kliknite na [EDIT]. U suprotnom koristite dugme Back vašeg web preglednika da se vratite na spisak izvještaja.</li>\r\n<li>Ako unosite konačne ocjene, s lijeve strane izaberite opciju <i>Konačna ocjena</i>.</li>\r\n<li>Ako unosite rezultate ispita, s lijeve strane izaberite opciju <i>Ispiti</i>, kreirajte novi ispit, a zatim kliknite na link <i>Masovni unos rezultata</i> pored novokreiranog ispita.</li>\r\n<li>Pozicionirajte kursor miša u polje ispod naslova <i>Masovni unos ocjena</i> i pritisnite Ctrl+V. Trebalo bi da ugledate rezultate ispita odnosno ocjene.</li>\r\n<li>Uvjerite se da pored natpisa <i>Format imena i prezimena</i> stoji <i>Prezime Ime</i> (a ne Prezime[TAB]Ime), te da pored <i>Separator</i> da stoji <i>TAB</i>.</li>\r\n<li>Kliknite na dugme <i>Dodaj</i>.</li>\r\n<li>Zamger će vam ponuditi još jednu priliku da provjerite da li su svi podaci uspravno uneseni. Ako jesu kliknite na dugme 
<i>Potvrda</i>.</li></ul>\r\n<p>Ovim su unesene ocjene / rezultati ispita!</p>\r\n\r\n\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 18-20 (masovni unos ispita) i str. 28-29 (masovni unos konačne ocjene).</i></p>', 'nastavnik'),
(15, '<p>...da kod evidencije prisustva, pored stanja "prisutan" (zelena boja) i stanja "odsutan" (crvena boja) postoji i nedefinisano stanje (žuta boja). Ovo stanje se dodjeljuje ako je student upisan u grupu nakon što su održani određeni časovi.</p>\r\n<p>Drečavo žuta boja je odabrana kako bi se predmetni nastavnik odnosno asistent podsjetio da se mora odlučiti da li će studentu priznati časove kao prisustva ili ne. U međuvremenu, nedefinisano stanje će se tumačiti u korist studenta, odnosno neće ulaziti u broj izostanaka prilikom određivanja da li je student izgubio bodove za prisustvo.</p>\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik'),
(16, '<p>...da ne morate voditi evidenciju o prisustvu kroz Zamger ako ne želite, a i dalje možete imati ažuran broj bodova ostvarenih na prisustvo?</p>\r\n<p>Sistem bodovanja je takav da student dobija 10 bodova ako je odsustvovao manje od 4 puta, a 0 bodova ako je odsustvovao 4 ili više puta. Podaci o konkretnim održanim časovima u Zamgeru se ne koriste nigdje osim za internu evidenciju na predmetu.</p>\r\n<p>Dakle, u slučaju da imate vlastitu evidenciju, samo kreirajte četiri časa (datum je nebitan) i unesite četiri izostanka studentima koji nisu zadovoljili prisustvo.</p>	\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 4-5.</i></p>', 'nastavnik'),
(17, '<p>...da možete podesiti drugačiji sistem bodovanja za prisustvo od ponuđenog?</p>\r\n<p>Možete podesiti ukupan broj bodova za prisustvo (različit od 10). Možete promijeniti maksimalan broj dozvoljenih izostanaka (različit od 3) ili pak podesiti linearno bodovanje u odnosu na broj izostanaka (npr. ako je student od 14 časova izostao 2 puta, dobiće (12/14)*10 = 8,6 bodova). Konačno, umjesto evidencije pojedinačnih časova, možete odabrati da direktno unosite broj bodova za prisustvo po uzoru na rezultate ispita.</p>\r\n<p>Da biste aktivirali ovu mogućnost, trebate promijeniti sistem bodovanja samog predmeta.</p>', 'nastavnik'),
(18, '<p>...da možete unijeti bodove za zadaću čak i ako je student nije poslao kroz Zamger?</p>\r\n<p>Da biste to uradili, potrebno je da kliknete na link <i>Prikaži dugmad za kreiranje zadataka</i> koji se nalazi u dnu stranice sa prikazom grupe (vidi sliku). Nakon što ovo uradite, ćelije tabele koje odgovaraju neposlanim zadaćama će se popuniti ikonama za kreiranje zadaće koje imaju oblik sijalice.</p>\r\n<p><a href="static/doc/savjet_sijalice.png" target="_new">Slika</a> - ukoliko ne vidite detalje, raširite prozor!</p>	\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 10-11.</i></p>\r\n<p>U slučaju da se na vašem predmetu zadaće generalno ne šalju kroz Zamger, vjerovatno će brži način rada za vas biti da koristite masovni unos. Više informacija na str. 27-28. Uputstava.</p>', 'nastavnik'),
(19, '<p>...da pomoću Zamgera možete poslati cirkularni mail svim studentima na vašem predmetu ili u pojedinim grupama?</p>\r\n<p>Da biste pristupili ovoj opciji:</p>\r\n<ul><li>Kliknite na link [EDIT] pored naziva predmeta</li>\r\n<li>U meniju sa lijeve strane odaberite opciju <i>Obavještenja za studente</i>.</li>\r\n<li>Pod menijem <i>Obavještenje za:</i> odaberite da li obavještenje šaljete svim studentima na predmetu ili samo studentima koji su članovi određene grupe.</li>\r\n<li>Aktivirajte opciju <i>Slanje e-maila</i>. Ako ova opcija nije aktivna, studenti će i dalje vidjeti vaše obavještenje na svojoj Zamger početnoj stranici (sekcija Obavještenja) kao i putem RSSa.</li>\r\n<li>U dio pod naslovom <i>Kraći tekst</i> unesite udarnu liniju vaše informacije.</li>\r\n<li>U dio pod naslovom <i>Detaljan tekst</i> možete napisati dodatna pojašnjenja, a možete ga i ostaviti praznim.</li>\r\n<li>Kliknite na dugme <i>Pošalji</i>. Vidjećete jedno po jedno ime studenta kojem je poslan mail kao i e-mail adresu na 
koju je mail poslan. Slanje veće količine mailova može potrajati nekoliko minuta.</li></ul>\r\n<p>Mailovi će biti poslani na adrese koje su studenti podesili koristeći svoj profil, ali i na zvanične fakultetske adrese.</p>\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 12-14.</i></p>', 'nastavnik'),
(20, '<p>...da je promjena grupe studenta destruktivna operacija kojom se nepovratno gube podaci o prisustvu studenta na časovima registrovanim za tu grupu?</p>\r\n<p>Studenta možete prebaciti u drugu grupu putem ekrana Dosje studenta: na pogledu grupe (npr. <i>Svi studenti</i>) kliknite na ime i prezime studenta da biste ušli u njegov ili njen dosje.</p>\r\n<p>Promjenom grupe nepovratno se gubi evidencija prisustva studenta na časovima registrovanim za prethodnu grupu. Naime, između časova registrovanih za dvije različite grupe ne postoji jednoznačno mapiranje. U nekom datom trenutku vremena u jednoj grupi može biti registrovano 10 časova a u drugoj 8. Kako znati koji od tih 10 časova odgovara kojem od onih 8? I šta raditi sa suvišnim časovima? Dakle, kada premjestite studenta u grupu u kojoj već postoje registrovani časovi, prisustvo studenta tim časovima će biti označeno kao nedefinisano (žuta boja). Prepušta se nastavnom ansamblu da odluči koje od tih časova će priznati kao prisutne, a koje markirati kao 
odsutne. Vjerovatno ćete se pitati šta ako se student ponovo vrati u polaznu grupu. Odgovor je da će podaci ponovo biti izgubljeni, jer šta raditi sa časovima registrovanim u međuvremenu?</p>\r\n<p>Preporučujemo da ne vršite promjene grupe nakon što počne akademska godina.</p>\r\n	\r\n<p><i>Više informacija u <a href="static/doc/zamger-uputstva-42-nastavnik.pdf" target="_new">Uputstvima za upotrebu</a>, str. 6.</i></p>', 'nastavnik');


--
-- Dumping data for table `strucni_stepen`
-- Šifrarnik - postoje još mnogi stručni stepeni koje nismo naveli, 
-- ovaj šifrarnik treba stalno dopunjavati
--

INSERT INTO `strucni_stepen` (`id`, `naziv`, `titula`) VALUES
(1, 'Magistar diplomirani inžinjer elektrotehnike', 'Mr. dipl. ing.'),
(2, 'Bakalaureat/Bachelor inžinjer elektrotehnike', 'BA ing.'),
(3, 'Diplomirani inženjer elektrotehnike', 'dipl.ing.el.'),
(4, 'Diplomirani matematičar', 'dipl.mat.'),
(5, 'Srednja stručna sprema', ''),
(6, 'Diplomirani inženjer mašinstva', 'dipl.ing.'),
(7, 'Diplomirani inženjer građevinarstva', 'dipl.ing.'),
(8, 'Diplomirani ekonomista', 'dipl.ecc.'),
(9, 'Diplomirani fizičar', 'dipl.fiz.');


--
-- Dumping data for table `studentski_modul`
-- Šifrarnik
--

INSERT INTO `studentski_modul` (`id`, `modul`, `gui_naziv`, `novi_prozor`) VALUES
(1, 'student/moodle', 'Materijali (Moodle)', 1),
(2, 'student/zadaca', 'Slanje zadaće', 0),
(3, 'izvjestaj/predmet', 'Dnevnik', 1),
(4, 'student/projekti', 'Projekti', 0);


--
-- Dumping data for table `studij`
-- Na nekoliko mjesta se pretpostavlja da postoji barem jedan
-- studij u sistemu (vidjeti i tabelu `institucija`)
--

INSERT INTO `studij` (`id`, `naziv`, `zavrsni_semestar`, `institucija`, `kratkinaziv`, `moguc_upis`, `tipstudija`, `preduslov`) VALUES
(1, 'Računarstvo i informatika (BSc)', 6, 2, 'RI', 1, 1, 1),
(2, 'Automatika i elektronika (BSc)', 6, 3, 'AE', 1, 1, 1),
(3, 'Elektroenergetika (BSc)', 6, 4, 'EE', 1, 1, 1),
(4, 'Telekomunikacije (BSc)', 6, 5, 'TK', 1, 1, 1);


--
-- Dumping data for table `svrha_potvrde`
-- Šifrarnik
--

INSERT INTO `svrha_potvrde` (`id`, `naziv`) VALUES
(1, 'regulisanja stipendije'),
(2, 'regulisanja prava na prevoz'),
(3, 'regulisanja zdravstvenog osiguranja'),
(4, 'regulisanja prava na šehidsku penziju'),
(5, 'regulisanja prava prijave na biro za zapošljavanje'),
(6, 'regulisanja socijalnog statusa'),
(7, 'regulisanja alimentacije'),
(8, 'regulisanja dječijeg dodatka'),
(9, 'regulisanja donacije'),
(10, 'regulisanja ferijalne prakse'),
(11, 'regulisanja penzije za civilne žrtve rata'),
(12, 'regulisanja prava na boračku penziju'),
(13, 'regulisanja prava na honorar'),
(14, 'regulisanja prava na invalidninu'),
(15, 'regulisanja prava na izdavanje pasoša'),
(16, 'regulisanja prava na poreske olakšice'),
(17, 'regulisanja prava na porodičnu penziju'),
(18, 'regulisanja prava na porodiljsku naknadu'),
(19, 'regulisanja prava na pristup internetu'),
(20, 'regulisanja prava na studentski dom'),
(21, 'regulisanja prava privremenog boravka'),
(22, 'regulisanja prava na izdavanje studentske kartice'),
(24, 'regulisanja radne vize'),
(25, 'regulisanja slobodnih dana za zaposlene studente'),
(26, 'regulisanja stambenog pitanja'),
(27, 'regulisanja statusnih pitanja'),
(29, 'regulisanja studentskog kredita'),
(30, 'regulisanja subvencije'),
(31, 'regulisanja turističke vize'),
(32, 'regulisanja vojne obaveze'),
(33, 'regulisanja vozačkog ispita'),
(35, 'učlanjenja u studentsku zadrugu'),
(36, 'regulisanja telekom usluga'),
(101, 'aplikacije na drugi fakultet'),
(102, 'hospitovanja u školi'),
(103, 'ostvarivanja prava na jednokratnu novčanu pomoć');


--
-- Dumping data for table `tippredmeta`
-- Šifrarnik koji se popunjava automatski
-- 1000 i 2000 su hardcodirane vrijednosti (FIXME?)
-- Tip 1 je standardni tip (vidjeti tabelu `komponenta`)
--

INSERT INTO `tippredmeta` (`id`, `naziv`) VALUES
(1, 'Bologna standard'),
(1000, 'Završni rad'),
(2000, 'Kolokvij');


--
-- Dumping data for table `tippredmeta_komponenta`
-- Komponente koje čine tip predmeta 'Bologna standard'
--

INSERT INTO `tippredmeta_komponenta` (`tippredmeta`, `komponenta`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6);


--
-- Dumping data for table `tipstudija`
-- Šifrarnik
--

INSERT INTO `tipstudija` (`id`, `naziv`, `ciklus`, `trajanje`, `moguc_upis`) VALUES
(1, 'Bakalaureat', 1, 6, 1),
(2, 'Master', 2, 4, 1),
(3, 'Doktorski studij', 3, 6, 1);


--
-- Dumping data for table `tip_potvrde`
-- Šifrarnik
--

INSERT INTO `tip_potvrde` (`id`, `naziv`) VALUES
(1, 'potvrda o redovnom studiju'),
(2, 'uvjerenje o položenim ispitima');


--
-- Dumping data for table `zvanje`
-- Šifrarnik
--

INSERT INTO `zvanje` (`id`, `naziv`, `titula`) VALUES
(1, 'Redovni profesor', 'R. prof.'),
(2, 'Vanredni profesor', 'V. prof.'),
(3, 'Docent', 'Doc.'),
(4, 'Viši asistent', 'V. asis.'),
(5, 'Asistent', 'Asis.'),
(6, 'Profesor emeritus', ''),
(7, 'predavač', 'pred.'),
(8, 'viši lektor', 'v. lec.'),
(9, 'lektor', 'lec.');
