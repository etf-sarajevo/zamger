class CreateLmsProjectProjectParams < ActiveRecord::Migration
  def change
    create_table :lms_project_project_params do |t|
      t.integer :course_unit_id
      t.integer :academic_year_id
      t.integer :min_teams
      t.integer :max_teams
      t.integer :min_team_members
      t.integer :max_team_members
      t.boolean :locked, :default => false

      t.timestamps
    end
  end
end
