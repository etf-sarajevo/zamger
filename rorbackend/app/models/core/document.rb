class Core::Document < ActiveRecord::Base
  has_many :enrollments
end
