class CreateLmsProjectProjects < ActiveRecord::Migration
  def change
    create_table :lms_project_projects do |t|
      t.string :name, :limit => 200
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.text :description
      t.text :note
      t.time :time

      # t.timestamps
    end
  end
end
