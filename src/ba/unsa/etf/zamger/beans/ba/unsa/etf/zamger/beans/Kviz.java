package ba.unsa.etf.zamger.beans;

// Generated May 26, 2015 12:09:40 PM by Hibernate Tools 3.4.0.CR1

import java.util.Date;
import java.util.HashSet;
import java.util.Set;

/**
 * Kviz generated by hbm2java
 */
public class Kviz implements java.io.Serializable {

	private Integer id;
	private Labgrupa labgrupa;
	private Predmet predmet;
	private AkademskaGodina akademskaGodina;
	private String naziv;
	private Date vrijemePocetak;
	private Date vrijemeKraj;
	private String ipAdrese;
	private float prolazBodova;
	private int brojPitanja;
	private int trajanjeKviza;
	private boolean aktivan;
	private Set<KvizPitanje> kvizPitanjes = new HashSet<KvizPitanje>(0);

	public Kviz() {
	}

	public Kviz(Predmet predmet, AkademskaGodina akademskaGodina, String naziv,
			Date vrijemePocetak, Date vrijemeKraj, String ipAdrese,
			float prolazBodova, int brojPitanja, int trajanjeKviza,
			boolean aktivan) {
		this.predmet = predmet;
		this.akademskaGodina = akademskaGodina;
		this.naziv = naziv;
		this.vrijemePocetak = vrijemePocetak;
		this.vrijemeKraj = vrijemeKraj;
		this.ipAdrese = ipAdrese;
		this.prolazBodova = prolazBodova;
		this.brojPitanja = brojPitanja;
		this.trajanjeKviza = trajanjeKviza;
		this.aktivan = aktivan;
	}

	public Kviz(Labgrupa labgrupa, Predmet predmet,
			AkademskaGodina akademskaGodina, String naziv, Date vrijemePocetak,
			Date vrijemeKraj, String ipAdrese, float prolazBodova,
			int brojPitanja, int trajanjeKviza, boolean aktivan,
			Set<KvizPitanje> kvizPitanjes) {
		this.labgrupa = labgrupa;
		this.predmet = predmet;
		this.akademskaGodina = akademskaGodina;
		this.naziv = naziv;
		this.vrijemePocetak = vrijemePocetak;
		this.vrijemeKraj = vrijemeKraj;
		this.ipAdrese = ipAdrese;
		this.prolazBodova = prolazBodova;
		this.brojPitanja = brojPitanja;
		this.trajanjeKviza = trajanjeKviza;
		this.aktivan = aktivan;
		this.kvizPitanjes = kvizPitanjes;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public Labgrupa getLabgrupa() {
		return this.labgrupa;
	}

	public void setLabgrupa(Labgrupa labgrupa) {
		this.labgrupa = labgrupa;
	}

	public Predmet getPredmet() {
		return this.predmet;
	}

	public void setPredmet(Predmet predmet) {
		this.predmet = predmet;
	}

	public AkademskaGodina getAkademskaGodina() {
		return this.akademskaGodina;
	}

	public void setAkademskaGodina(AkademskaGodina akademskaGodina) {
		this.akademskaGodina = akademskaGodina;
	}

	public String getNaziv() {
		return this.naziv;
	}

	public void setNaziv(String naziv) {
		this.naziv = naziv;
	}

	public Date getVrijemePocetak() {
		return this.vrijemePocetak;
	}

	public void setVrijemePocetak(Date vrijemePocetak) {
		this.vrijemePocetak = vrijemePocetak;
	}

	public Date getVrijemeKraj() {
		return this.vrijemeKraj;
	}

	public void setVrijemeKraj(Date vrijemeKraj) {
		this.vrijemeKraj = vrijemeKraj;
	}

	public String getIpAdrese() {
		return this.ipAdrese;
	}

	public void setIpAdrese(String ipAdrese) {
		this.ipAdrese = ipAdrese;
	}

	public float getProlazBodova() {
		return this.prolazBodova;
	}

	public void setProlazBodova(float prolazBodova) {
		this.prolazBodova = prolazBodova;
	}

	public int getBrojPitanja() {
		return this.brojPitanja;
	}

	public void setBrojPitanja(int brojPitanja) {
		this.brojPitanja = brojPitanja;
	}

	public int getTrajanjeKviza() {
		return this.trajanjeKviza;
	}

	public void setTrajanjeKviza(int trajanjeKviza) {
		this.trajanjeKviza = trajanjeKviza;
	}

	public boolean isAktivan() {
		return this.aktivan;
	}

	public void setAktivan(boolean aktivan) {
		this.aktivan = aktivan;
	}

	public Set<KvizPitanje> getKvizPitanjes() {
		return this.kvizPitanjes;
	}

	public void setKvizPitanjes(Set<KvizPitanje> kvizPitanjes) {
		this.kvizPitanjes = kvizPitanjes;
	}

}
