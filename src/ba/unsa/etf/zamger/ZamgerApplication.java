package ba.unsa.etf.zamger;

import java.util.HashSet;
import java.util.Set;

import javax.ws.rs.core.Application;

import ba.unsa.etf.zamger.resources.*;


//Jackson imports
import com.fasterxml.jackson.jaxrs.json.JacksonJaxbJsonProvider;
import com.fasterxml.jackson.databind.AnnotationIntrospector;
import com.fasterxml.jackson.databind.ObjectMapper;
import com.fasterxml.jackson.databind.introspect.JacksonAnnotationIntrospector;
import com.fasterxml.jackson.module.jaxb.JaxbAnnotationIntrospector;
import com.fasterxml.jackson.databind.type.TypeFactory;

public class ZamgerApplication extends Application {
	@Override
	public Set<Class<?>> getClasses() {
		Set<Class<?>> classes = new HashSet<Class<?>>();
		classes.add(OsobaResource.class);
		classes.add(AuthResource.class);
		classes.add(TokenResource.class);
		return classes;
	}
	 @Override
	  public Set<Object> getSingletons() {
	    Set<Object> s = new HashSet<Object>();

	    // Make (de)serializer use a subset of JAXB and (afterwards) Jackson annotations
	    // See http://wiki.fasterxml.com/JacksonJAXBAnnotations for more information
	    ObjectMapper mapper = new ObjectMapper();
	    AnnotationIntrospector primary = new JaxbAnnotationIntrospector( TypeFactory.defaultInstance() );
	    AnnotationIntrospector secondary = new JacksonAnnotationIntrospector();
	    AnnotationIntrospector pair = AnnotationIntrospector.pair(primary, secondary);
	    mapper.getDeserializationConfig().with(pair);
	    mapper.getSerializationConfig().with(pair);

	    // Set up the provider
	    JacksonJaxbJsonProvider jaxbProvider = new JacksonJaxbJsonProvider(); //
	    jaxbProvider.setMapper(mapper);

	    s.add(jaxbProvider);
	    return s;
	  }
}
