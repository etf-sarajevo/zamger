# Load the rails application
require File.expand_path('../application', __FILE__)

    Resque.after_fork do |job|
        logger.info "HALID je bio ovdjeee"
        ActiveRecord::Base.connection.execute("EXEC SQL DISCONNECT CURRENT")
	ActiveRecord::Base.connection.connect
    end

Zamger::Application.initialize!
