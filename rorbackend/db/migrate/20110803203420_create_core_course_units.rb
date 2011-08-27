class CreateCoreCourseUnits < ActiveRecord::Migration
  def change
    create_table :core_course_units do |t|
      t.string :code, :limit => 20
      t.string :name, :limit => 100
      t.string :short_name, :limit => 10
      t.integer :institution_id, :default => 0
      t.integer :course_unit_type_id
      t.float :ects
      
      # t.timestamps
    end
  end
end
