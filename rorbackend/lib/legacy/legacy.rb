module Legacy
  def self.append_features(base)
    super
    base.extend(ClassMethods)
  end
  module ClassMethods
    def alias_column(options)
      options.each do |new_name, old_name|
        self.send(:define_method, new_name) { self.send(old_name) }
        self.send(:define_method, "#{new_name}=") { |value| self.send("#{old_name}=", value) }
      end
    end
  end
end

ActiveRecord::Base.class_eval do
  include Legacy
end