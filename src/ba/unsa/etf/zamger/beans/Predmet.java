package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;
import java.util.HashSet;
import java.util.Set;

/**
 * Predmet generated by hbm2java
 */
public class Predmet implements java.io.Serializable {

	private Integer id;
	private Institucija institucija;
	private String sifra;
	private String naziv;
	private String kratkiNaziv;
	private int tippredmeta;
	private float ects;
	private boolean mogucUpis;
	private Date objavljeniRezultati;
	private int satiNastave;
	private Set<AkademskaGodinaPredmet> akademskaGodinaPredmets = new HashSet<AkademskaGodinaPredmet>(
			0);
	private Set<Ispit> ispits = new HashSet<Ispit>(0);
	private Set<Projekat> projekats = new HashSet<Projekat>(0);
	private Set<KonacnaOcjena> konacnaOcjenas = new HashSet<KonacnaOcjena>(0);
	private Set<Ponudakursa> ponudakursas = new HashSet<Ponudakursa>(0);
	private Set<Kviz> kvizs = new HashSet<Kviz>(0);
	private Set<AnketaAnketa> anketaAnketas = new HashSet<AnketaAnketa>(0);
	private Set<NastavnikPredmet> nastavnikPredmets = new HashSet<NastavnikPredmet>(
			0);
	private Set<GgPredmet> ggPredmets = new HashSet<GgPredmet>(0);
	private Set<Kolizija> kolizijas = new HashSet<Kolizija>(0);
	private Set<Angazman> angazmans = new HashSet<Angazman>(0);
	private Set<IzborniSlot> izborniSlots = new HashSet<IzborniSlot>(0);
	private Set<Labgrupa> labgrupas = new HashSet<Labgrupa>(0);
	private Set<AnketaPredmet> anketaPredmets = new HashSet<AnketaPredmet>(0);

	public Predmet() {
	}

	public Predmet(Institucija institucija, String sifra, String naziv,
			String kratkiNaziv, int tippredmeta, float ects, boolean mogucUpis,
			Date objavljeniRezultati, int satiNastave) {
		this.institucija = institucija;
		this.sifra = sifra;
		this.naziv = naziv;
		this.kratkiNaziv = kratkiNaziv;
		this.tippredmeta = tippredmeta;
		this.ects = ects;
		this.mogucUpis = mogucUpis;
		this.objavljeniRezultati = objavljeniRezultati;
		this.satiNastave = satiNastave;
	}

	public Predmet(Institucija institucija, String sifra, String naziv,
			String kratkiNaziv, int tippredmeta, float ects, boolean mogucUpis,
			Date objavljeniRezultati, int satiNastave,
			Set<AkademskaGodinaPredmet> akademskaGodinaPredmets,
			Set<Ispit> ispits, Set<Projekat> projekats,
			Set<KonacnaOcjena> konacnaOcjenas, Set<Ponudakursa> ponudakursas,
			Set<Kviz> kvizs, Set<AnketaAnketa> anketaAnketas,
			Set<NastavnikPredmet> nastavnikPredmets, Set<GgPredmet> ggPredmets,
			Set<Kolizija> kolizijas, Set<Angazman> angazmans,
			Set<IzborniSlot> izborniSlots, Set<Labgrupa> labgrupas,
			Set<AnketaPredmet> anketaPredmets) {
		this.institucija = institucija;
		this.sifra = sifra;
		this.naziv = naziv;
		this.kratkiNaziv = kratkiNaziv;
		this.tippredmeta = tippredmeta;
		this.ects = ects;
		this.mogucUpis = mogucUpis;
		this.objavljeniRezultati = objavljeniRezultati;
		this.satiNastave = satiNastave;
		this.akademskaGodinaPredmets = akademskaGodinaPredmets;
		this.ispits = ispits;
		this.projekats = projekats;
		this.konacnaOcjenas = konacnaOcjenas;
		this.ponudakursas = ponudakursas;
		this.kvizs = kvizs;
		this.anketaAnketas = anketaAnketas;
		this.nastavnikPredmets = nastavnikPredmets;
		this.ggPredmets = ggPredmets;
		this.kolizijas = kolizijas;
		this.angazmans = angazmans;
		this.izborniSlots = izborniSlots;
		this.labgrupas = labgrupas;
		this.anketaPredmets = anketaPredmets;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public Institucija getInstitucija() {
		return this.institucija;
	}

	public void setInstitucija(Institucija institucija) {
		this.institucija = institucija;
	}

	public String getSifra() {
		return this.sifra;
	}

	public void setSifra(String sifra) {
		this.sifra = sifra;
	}

	public String getNaziv() {
		return this.naziv;
	}

	public void setNaziv(String naziv) {
		this.naziv = naziv;
	}

	public String getKratkiNaziv() {
		return this.kratkiNaziv;
	}

	public void setKratkiNaziv(String kratkiNaziv) {
		this.kratkiNaziv = kratkiNaziv;
	}

	public int getTippredmeta() {
		return this.tippredmeta;
	}

	public void setTippredmeta(int tippredmeta) {
		this.tippredmeta = tippredmeta;
	}

	public float getEcts() {
		return this.ects;
	}

	public void setEcts(float ects) {
		this.ects = ects;
	}

	public boolean isMogucUpis() {
		return this.mogucUpis;
	}

	public void setMogucUpis(boolean mogucUpis) {
		this.mogucUpis = mogucUpis;
	}

	public Date getObjavljeniRezultati() {
		return this.objavljeniRezultati;
	}

	public void setObjavljeniRezultati(Date objavljeniRezultati) {
		this.objavljeniRezultati = objavljeniRezultati;
	}

	public int getSatiNastave() {
		return this.satiNastave;
	}

	public void setSatiNastave(int satiNastave) {
		this.satiNastave = satiNastave;
	}

	public Set<AkademskaGodinaPredmet> getAkademskaGodinaPredmets() {
		return this.akademskaGodinaPredmets;
	}

	public void setAkademskaGodinaPredmets(
			Set<AkademskaGodinaPredmet> akademskaGodinaPredmets) {
		this.akademskaGodinaPredmets = akademskaGodinaPredmets;
	}

	public Set<Ispit> getIspits() {
		return this.ispits;
	}

	public void setIspits(Set<Ispit> ispits) {
		this.ispits = ispits;
	}

	public Set<Projekat> getProjekats() {
		return this.projekats;
	}

	public void setProjekats(Set<Projekat> projekats) {
		this.projekats = projekats;
	}

	public Set<KonacnaOcjena> getKonacnaOcjenas() {
		return this.konacnaOcjenas;
	}

	public void setKonacnaOcjenas(Set<KonacnaOcjena> konacnaOcjenas) {
		this.konacnaOcjenas = konacnaOcjenas;
	}

	public Set<Ponudakursa> getPonudakursas() {
		return this.ponudakursas;
	}

	public void setPonudakursas(Set<Ponudakursa> ponudakursas) {
		this.ponudakursas = ponudakursas;
	}

	public Set<Kviz> getKvizs() {
		return this.kvizs;
	}

	public void setKvizs(Set<Kviz> kvizs) {
		this.kvizs = kvizs;
	}

	public Set<AnketaAnketa> getAnketaAnketas() {
		return this.anketaAnketas;
	}

	public void setAnketaAnketas(Set<AnketaAnketa> anketaAnketas) {
		this.anketaAnketas = anketaAnketas;
	}

	public Set<NastavnikPredmet> getNastavnikPredmets() {
		return this.nastavnikPredmets;
	}

	public void setNastavnikPredmets(Set<NastavnikPredmet> nastavnikPredmets) {
		this.nastavnikPredmets = nastavnikPredmets;
	}

	public Set<GgPredmet> getGgPredmets() {
		return this.ggPredmets;
	}

	public void setGgPredmets(Set<GgPredmet> ggPredmets) {
		this.ggPredmets = ggPredmets;
	}

	public Set<Kolizija> getKolizijas() {
		return this.kolizijas;
	}

	public void setKolizijas(Set<Kolizija> kolizijas) {
		this.kolizijas = kolizijas;
	}

	public Set<Angazman> getAngazmans() {
		return this.angazmans;
	}

	public void setAngazmans(Set<Angazman> angazmans) {
		this.angazmans = angazmans;
	}

	public Set<IzborniSlot> getIzborniSlots() {
		return this.izborniSlots;
	}

	public void setIzborniSlots(Set<IzborniSlot> izborniSlots) {
		this.izborniSlots = izborniSlots;
	}

	public Set<Labgrupa> getLabgrupas() {
		return this.labgrupas;
	}

	public void setLabgrupas(Set<Labgrupa> labgrupas) {
		this.labgrupas = labgrupas;
	}

	public Set<AnketaPredmet> getAnketaPredmets() {
		return this.anketaPredmets;
	}

	public void setAnketaPredmets(Set<AnketaPredmet> anketaPredmets) {
		this.anketaPredmets = anketaPredmets;
	}

}
