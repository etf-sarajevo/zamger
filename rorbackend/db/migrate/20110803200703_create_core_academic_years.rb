class CreateCoreAcademicYears < ActiveRecord::Migration
  def change
    create_table :core_academic_years do |t|
      t.string :name, :limit => 20
      t.boolean :current

      # t.timestamps
    end
    
    add_index :core_academic_years, :name, :unique => true, :name => "unique_index_core_academic_years_name"
    add_index :core_academic_years, :current
    
  end
end
