package ba.unsa.etf.zamger.beans;

// Generated May 20, 2015 3:15:09 PM by Hibernate Tools 3.4.0.CR1

import java.util.HashSet;
import java.util.Set;
import com.fasterxml.jackson.annotation.*;

/**
 * Drzava generated by hbm2java
 */
@JsonIgnoreProperties({"hibernateLazyInitializer", "handler"})
public class Drzava implements java.io.Serializable {

	private Integer id;
	private String naziv;
	@JsonIgnore
	private Set<Mjesto> mjestos = new HashSet<Mjesto>(0);

	public Drzava() {
	}

	public Drzava(String naziv) {
		this.naziv = naziv;
	}

	public Drzava(String naziv, Set<Mjesto> mjestos) {
		this.naziv = naziv;
		this.mjestos = mjestos;
	}

	public Integer getId() {
		return this.id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public String getNaziv() {
		return this.naziv;
	}

	public void setNaziv(String naziv) {
		this.naziv = naziv;
	}

	@JsonIgnore
	public Set<Mjesto> getMjestos() {
		return this.mjestos;
	}

	@JsonIgnore
	public void setMjestos(Set<Mjesto> mjestos) {
		this.mjestos = mjestos;
	}

}
