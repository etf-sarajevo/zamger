package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

/**
 * StudentOld generated by hbm2java
 */
public class StudentOld implements java.io.Serializable {

	private long id;
	private String ime;
	private String prezime;
	private String email;
	private String brindexa;

	public StudentOld() {
	}

	public StudentOld(long id, String ime, String prezime, String email,
			String brindexa) {
		this.id = id;
		this.ime = ime;
		this.prezime = prezime;
		this.email = email;
		this.brindexa = brindexa;
	}

	public long getId() {
		return this.id;
	}

	public void setId(long id) {
		this.id = id;
	}

	public String getIme() {
		return this.ime;
	}

	public void setIme(String ime) {
		this.ime = ime;
	}

	public String getPrezime() {
		return this.prezime;
	}

	public void setPrezime(String prezime) {
		this.prezime = prezime;
	}

	public String getEmail() {
		return this.email;
	}

	public void setEmail(String email) {
		this.email = email;
	}

	public String getBrindexa() {
		return this.brindexa;
	}

	public void setBrindexa(String brindexa) {
		this.brindexa = brindexa;
	}

}
